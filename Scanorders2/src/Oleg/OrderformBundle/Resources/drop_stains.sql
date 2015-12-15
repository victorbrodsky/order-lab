-- Stain list has two dependencies:
-- scan_stain and scan_blockSpecialStain tables which have to be dropped first before dropping scan_stainList table

DROP TABLE dbo.scan_blockSpecialStain;
DROP TABLE dbo.scan_stain;
DROP TABLE dbo.scan_stainlist;
