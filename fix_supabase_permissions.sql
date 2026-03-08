-- Run this entirely in your Supabase SQL Editor to fix the "permission denied for schema public" error
-- and to ensure the anon role can access your citizen_account and person tables!

-- 1. Grant usage on the public schema to anon and authenticated roles
GRANT USAGE ON SCHEMA public TO anon, authenticated;

-- 2. Grant all privileges on all tables in the public schema to anon and authenticated
GRANT ALL ON ALL TABLES IN SCHEMA public TO anon, authenticated;

-- 3. Grant all privileges on all sequences (for auto-incrementing IDs) to anon and authenticated
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO anon, authenticated;

-- 4. Enable Row Level Security (RLS) and allow all access for the citizen_account table
ALTER TABLE citizen_account ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "allow_all_citizen" ON citizen_account;
CREATE POLICY "allow_all_citizen" ON citizen_account FOR ALL USING (true) WITH CHECK (true);

-- 5. Enable Row Level Security (RLS) and allow all access for the person table
ALTER TABLE person ENABLE ROW LEVEL SECURITY;
DROP POLICY IF EXISTS "allow_all_person" ON person;
CREATE POLICY "allow_all_person" ON person FOR ALL USING (true) WITH CHECK (true);

-- 6. Ensure default privileges for any future tables you might create
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO anon, authenticated;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO anon, authenticated;
