-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2018 at 12:09 PM
-- Server version: 5.6.14
-- PHP Version: 5.3.13

-- SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
-- SET time_zone = "+00:00";
SET IDENTITY_INSERT transres_antibodyList ON;

INSERT INTO transres_antibodyList (id, category, name, altname, company, catalog, lot, igconcentration, clone, host, reactivity, control, protocol, retrieval, dilution, storage, comment, datasheet, pdf) VALUES
(1, 'M', 'Androgen Receptor', 'AR ', 'Abcam', 'ab74272', 'GR32463-1', '0.2 mg/ml', 'Poly', 'Rabbit ', 'Human, mouse', 'Xenograft Control/Prostate Ca.', 'Envision Rabbit R. ', 'H130', '1:200', '-20 oC', 'Project: 12743 RS#: 30323 PI: Rubin/Kyung Condition confirmed by Dr. Rubin/Kyung on 03/09/2011', 'http://www.abcam.com/Androgen-Receptor-antibody-ab74272.html', 'upload/pdf/1296507249.pdf');

INSERT INTO transres_antibodyList (id, category, name, altname, company, catalog, lot, igconcentration, clone, host, reactivity, control, protocol, retrieval, dilution, storage, comment, datasheet, pdf) VALUES
(3, 'M', 'Bcl-6 -  Rabbit Anti-mouse', NULL, 'Santa Cruz', 'Bcl6 (sc-858)', '', '200 ug/ml', 'Poly', 'Rabbit', 'Mouse, human, rat', 'I08-995 A1 (#22)) Flip CD19 Promotor/', 'Envision Rabbit Refine', 'H230', '1 to 50', '4C', 'Project: 10820 RS#:  PI: Cesarman ', 'http://www.scbt.com/datasheet-858-bcl-6-n-3-antibody.html', ' ');


SET IDENTITY_INSERT transres_antibodyList OFF;