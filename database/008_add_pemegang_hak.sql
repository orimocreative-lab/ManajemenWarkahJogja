-- Alter table to add pemegang_hak column
ALTER TABLE bon_warkah ADD COLUMN pemegang_hak VARCHAR(255) AFTER peminjam;
ALTER TABLE warkah ADD COLUMN pemegang_hak VARCHAR(255) AFTER peminjam;