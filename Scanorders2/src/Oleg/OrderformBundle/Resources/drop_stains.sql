--     Copyright 2017 Cornell University
--
--     Licensed under the Apache License, Version 2.0 (the "License");
--     you may not use this file except in compliance with the License.
--     You may obtain a copy of the License at
--
--     http://www.apache.org/licenses/LICENSE-2.0
--
--     Unless required by applicable law or agreed to in writing, software
--     distributed under the License is distributed on an "AS IS" BASIS,
--     WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
--     See the License for the specific language governing permissions and
--     limitations under the License.

-- Stain list has two dependencies:
-- scan_stain and scan_blockSpecialStain tables which have to be dropped first before dropping scan_stainList table

DROP TABLE [ScanOrder].[dbo].[scan_blockSpecialStains];
DROP TABLE [ScanOrder].[dbo].[scan_stain];
DROP TABLE [ScanOrder].[dbo].[scan_stainlist];
