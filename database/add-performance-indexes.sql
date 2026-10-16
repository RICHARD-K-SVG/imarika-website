CREATE INDEX IF NOT EXISTS idx_admissions_submitted_at
  ON admissions (submitted_at);

CREATE INDEX IF NOT EXISTS idx_inquiries_submitted_at
  ON inquiries (submitted_at);

CREATE INDEX IF NOT EXISTS idx_news_pinned_created_at
  ON news (pinned, created_at);
