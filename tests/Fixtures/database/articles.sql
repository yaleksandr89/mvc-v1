-- Deterministic synthetic data for the isolated mvc_v1_test database.
BEGIN;

INSERT INTO public.blog_posts (
    id,
    title,
    excerpt,
    content_html,
    published_at,
    updated_at
) VALUES
    (1,
     'Case Insensitive PostgreSQL Lookup',
     'A deterministic excerpt long enough to exercise the first database-backed article contract.',
     'This synthetic article body is intentionally longer than one hundred characters so validation and rendering tests can rely on a stable fixture without borrowing demo content.',
     '2026-01-10 09:00:00',
     '2026-01-10 09:00:00'),
    (2,
     'Reliable Fixture Reset Contract',
     'A second deterministic excerpt that documents isolation between individual integration tests.',
     'This second synthetic body provides predictable database content and enough characters for the application validation boundary while remaining clearly test-only material.',
     '2026-02-11 10:30:00',
     '2026-02-12 11:45:00'),
    (3,
     'Framework Dispatch Happy Path',
     'A third deterministic excerpt used when checking descending pagination and routed article display.',
     'This third synthetic body gives the real controller and view a recognizable safe value to render through Dispatcher, Page, View, and the default application layout.',
     '2026-03-12 12:15:00',
     '2026-03-13 13:20:00');

SELECT setval(
    pg_get_serial_sequence('public.blog_posts', 'id'),
    (SELECT MAX(id) FROM public.blog_posts),
    true
);

COMMIT;
