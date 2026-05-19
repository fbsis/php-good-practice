<?php

declare(strict_types=1);

namespace Challenge\Repositories;

use PDO;

final class VisitorAnalyticsRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Returns active visitors for an account.
     *
     * @return list<array<string, scalar|null>>
     */
    public function activeVisitors(int $accountId, string $from, string $to): array
    {
        $statement = $this->pdo->prepare(
            <<<'SQL'
            SELECT
                v.external_id AS visitor_id,
                ie.email AS email,
                ie.company AS company,
                pv.page_view_count AS page_view_count,
                pv.last_seen_at AS last_seen_at,
                pv.page_view_count + (CASE WHEN ie.email IS NULL THEN 0 ELSE 10 END) AS engagement_score
            FROM visitors v
            INNER JOIN (
                SELECT
                    visitor_id,
                    COUNT(*) AS page_view_count,
                    MAX(occurred_at) AS last_seen_at
                FROM page_views
                WHERE occurred_at >= :from_date
                  AND occurred_at <= :to_date
                GROUP BY visitor_id
            ) pv ON pv.visitor_id = v.id
            LEFT JOIN identity_events ie ON ie.id = (
                SELECT latest_identity.id
                FROM identity_events latest_identity
                WHERE latest_identity.visitor_id = v.id
                ORDER BY latest_identity.occurred_at DESC, latest_identity.id DESC
                LIMIT 1
            )
            WHERE v.account_id = :account_id
            ORDER BY pv.last_seen_at DESC, engagement_score DESC
            SQL
        );

        $statement->execute([
            'account_id' => $accountId,
            'from_date' => $from . ' 00:00:00',
            'to_date' => $to . ' 23:59:59',
        ]);

        return $statement->fetchAll();
    }
}
