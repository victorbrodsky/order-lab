-- Stain list has two dependencies:
-- scan_stain and scan_blockSpecialStain tables which have to be dropped first before dropping scan_stainList table

DROP TABLE [ScanOrder].[dbo].[scan_blockSpecialStains];
DROP TABLE [ScanOrder].[dbo].[scan_stain];
DROP TABLE [ScanOrder].[dbo].[scan_stainlist];
