-- PrestaShop side of the acceptance tests. Run with:
--   docker compose -f staging/docker-compose.yml exec -T db mysql -uroot -p"$DB_PASSWORD" prestashop < staging/check.sql
-- Set @email first:  SET @email = 'a@example.test';
SELECT id_customer, id_shop, email, newsletter, optin, active, deleted, is_guest FROM ps_customer WHERE email = @email;
SELECT id, id_shop, email, active FROM ps_emailsubscription WHERE email = @email;           -- guests (ps_emailsubscription module)
SELECT * FROM ps_mj_pending_operation ORDER BY 1 DESC LIMIT 20;                           -- retry queue (DE-11715)
SELECT * FROM ps_mj_events ORDER BY 1 DESC LIMIT 20;                                      -- webhook events (DE-11722)
