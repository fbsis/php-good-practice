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
     * @return list<array<string, mixed>>
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

    /**
     * @param array{
     *     visited_path: string,
     *     min_page_views: int,
     *     identified_only: bool,
     *     from: string,
     *     to: string,
     *     limit: int
     * } $rules
     */
    public function countSegmentPreviewVisitors(int $accountId, array $rules): int
    {
        $statement = $this->pdo->prepare(
            <<<'SQL'
            SELECT COUNT(*)
            FROM (
                SELECT v.id
                FROM visitors v
                INNER JOIN page_views pv
                    ON pv.visitor_id = v.id
                    AND pv.occurred_at >= :pv_from_date
                    AND pv.occurred_at <= :pv_to_date
                LEFT JOIN identity_events ie ON ie.id = (
                    SELECT latest_identity.id
                    FROM identity_events latest_identity
                    WHERE latest_identity.visitor_id = v.id
                    ORDER BY latest_identity.occurred_at DESC, latest_identity.id DESC
                    LIMIT 1
                )
                WHERE v.account_id = :account_id
                  AND (:identified_only = 0 OR ie.email IS NOT NULL)
                  AND EXISTS (
                      SELECT 1
                      FROM page_views matching_page_view
                      WHERE matching_page_view.visitor_id = v.id
                        AND matching_page_view.path = :visited_path
                        AND matching_page_view.occurred_at >= :match_from_date
                        AND matching_page_view.occurred_at <= :match_to_date
                  )
                GROUP BY v.id
                HAVING COUNT(pv.id) >= :min_page_views
            ) matching_visitors
            SQL
        );

        $this->bindSegmentPreviewValues($statement, $accountId, $rules);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    /**
     * @param array{
     *     visited_path: string,
     *     min_page_views: int,
     *     identified_only: bool,
     *     from: string,
     *     to: string,
     *     limit: int
     * } $rules
     * @return list<array<string, mixed>>
     */
    public function segmentPreviewVisitors(int $accountId, array $rules): array
    {
        $statement = $this->pdo->prepare(
            <<<'SQL'
            SELECT
                v.external_id AS visitor_id,
                ie.email AS email,
                ie.company AS company,
                COUNT(pv.id) AS page_view_count,
                MAX(pv.occurred_at) AS last_seen_at
            FROM visitors v
            INNER JOIN page_views pv
                ON pv.visitor_id = v.id
                AND pv.occurred_at >= :pv_from_date
                AND pv.occurred_at <= :pv_to_date
            LEFT JOIN identity_events ie ON ie.id = (
                SELECT latest_identity.id
                FROM identity_events latest_identity
                WHERE latest_identity.visitor_id = v.id
                ORDER BY latest_identity.occurred_at DESC, latest_identity.id DESC
                LIMIT 1
            )
            WHERE v.account_id = :account_id
              AND (:identified_only = 0 OR ie.email IS NOT NULL)
              AND EXISTS (
                  SELECT 1
                  FROM page_views matching_page_view
                  WHERE matching_page_view.visitor_id = v.id
                    AND matching_page_view.path = :visited_path
                    AND matching_page_view.occurred_at >= :match_from_date
                    AND matching_page_view.occurred_at <= :match_to_date
              )
            GROUP BY v.id, v.external_id, ie.email, ie.company
            HAVING COUNT(pv.id) >= :min_page_views
            ORDER BY last_seen_at DESC, page_view_count DESC, v.external_id ASC
            LIMIT :limit
            SQL
        );

        $this->bindSegmentPreviewValues($statement, $accountId, $rules);
        $statement->bindValue('limit', $rules['limit'], PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @param array{
     *     visited_path: string,
     *     min_page_views: int,
     *     identified_only: bool,
     *     from: string,
     *     to: string,
     *     limit: int
     * } $rules
     */
    private function bindSegmentPreviewValues(\PDOStatement $statement, int $accountId, array $rules): void
    {
        $statement->bindValue('account_id', $accountId, PDO::PARAM_INT);
        $statement->bindValue('visited_path', $rules['visited_path']);
        $statement->bindValue('pv_from_date', $rules['from'] . ' 00:00:00');
        $statement->bindValue('pv_to_date', $rules['to'] . ' 23:59:59');
        $statement->bindValue('match_from_date', $rules['from'] . ' 00:00:00');
        $statement->bindValue('match_to_date', $rules['to'] . ' 23:59:59');
        $statement->bindValue('min_page_views', $rules['min_page_views'], PDO::PARAM_INT);
        $statement->bindValue('identified_only', $rules['identified_only'] ? 1 : 0, PDO::PARAM_INT);
    }
}
