<?php
/**
 * Copyright (c) 2020 Cornell University
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

//Credit to cirovargas https://gist.github.com/cirovargas/341eb03d6d9e5967bd4991b4de94e51b

namespace App\ResAppBundle\PdfParser;

class PDFService
{
    /**
     * Split PDF file
     *
     * <p>Split all of the pages from a larger PDF files into
     * single-page PDF files.</p>
     *
     * @package FPDF required http://www.fpdf.org/
     * @package FPDI required http://www.setasign.de/products/pdf-php-solutions/fpdi/
     * @param   string $filename      The filename of the PDF to split
     * @param   string $end_directory The end directory for split PDF (original PDF's directory by default)
     * @return  void
     */
    function split_pdf($filename, $end_directory = false)
    {

        $end_directory = $end_directory ? $end_directory : './';
        $new_path = preg_replace('/[\/]+/', '/', $end_directory.'/'.substr((string)$filename, 0, strrpos($filename, '/')));

        if (!is_dir($new_path)) {
            // Will make directories under end directory that don't exist
            // Provided that end directory exists and has the right permissions
            @mkdir($new_path, 0777, true);
        }

        $pdf = new \FPDI();
        $pagecount = $pdf->setSourceFile($filename); // How many pages?

        $return = array();

        // Split each page into a new PDF
        for ($i = 1; $i <= $pagecount; $i++) {
            $new_pdf = new \FPDI();
            $new_pdf->AddPage();
            $new_pdf->setSourceFile($filename);
            $new_pdf->useTemplate($new_pdf->importPage($i));

            try {
                $new_filename = $end_directory.str_replace('.pdf', '', basename($filename)).'_'.$i.".pdf";
                $return[] = str_replace('.pdf', '', basename($filename)).'_'.$i.".pdf";
                $new_pdf->Output($new_filename, "F");
                //echo "Page ".$i." split into ".$new_filename."<br />\n";
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        }

        return $return;
    }

    public function parseFile($file)
    {
        return PDFParser::parseFile($file);
    }

    function decodeAsciiHex($input)
    {
        $output = "";

        $isOdd = true;
        $isComment = false;

        for ($i = 0, $codeHigh = -1; $i < strlen((string)$input) && $input[$i] != '>'; $i++) {
            $c = $input[$i];

            if ($isComment) {
                if ($c == '\r' || $c == '\n') {
                    $isComment = false;
                }
                continue;
            }

            switch ($c) {
                case '\0':
                case '\t':
                case '\r':
                case '\f':
                case '\n':
                case ' ':
                    break;
                case '%':
                    $isComment = true;
                    break;

                default:
                    $code = hexdec($c);
                    if ($code === 0 && $c != '0') {
                        return "";
                    }

                    if ($isOdd) {
                        $codeHigh = $code;
                    } else {
                        $output .= chr($codeHigh * 16 + $code);
                    }

                        $isOdd = !$isOdd;
                    break;
            }
        }

        if ($input[$i] != '>') {
            return "";
        }

        if ($isOdd) {
            $output .= chr($codeHigh * 16);
        }

        return $output;
    }
    function decodeAscii85($input)
    {
        $output = "";

        $isComment = false;
        $ords = array();

        for ($i = 0, $state = 0; $i < strlen((string)$input) && $input[$i] != '~'; $i++) {
            $c = $input[$i];

            if ($isComment) {
                if ($c == '\r' || $c == '\n') {
                    $isComment = false;
                }
                continue;
            }

            if ($c == '\0' || $c == '\t' || $c == '\r' || $c == '\f' || $c == '\n' || $c == ' ') {
                continue;
            }
            if ($c == '%') {
                $isComment = true;
                continue;
            }
            if ($c == 'z' && $state === 0) {
                $output .= str_repeat(chr(0), 4);
                continue;
            }
            if ($c < '!' || $c > 'u') {
                return "";
            }

            $code = ord($input[$i]) & 0xff;
            $ords[$state++] = $code - ord('!');

            if ($state == 5) {
                $state = 0;
                for ($sum = 0, $j = 0; $j < 5; $j++) {
                    $sum = $sum * 85 + $ords[$j];
                }
                for ($j = 3; $j >= 0; $j--) {
                    $output .= chr($sum >> ($j * 8));
                }
            }
        }
        if ($state === 1) {
            return "";
        } elseif ($state > 1) {
            for ($i = 0, $sum = 0; $i < $state; $i++) {
                $sum += ($ords[$i] + ($i == $state - 1)) * pow(85, 4 - $i);
            }
            for ($i = 0; $i < $state - 1; $i++) {
                $output .= chr($sum >> ((3 - $i) * 8));
            }
        }

        return $output;
    }
    function decodeFlate($input)
    {
        return @gzuncompress($input);
    }

    public function getObjectOptions($object)
    {
        $options = array();
        if (preg_match("#<<(.*)>>#ismU", $object, $options)) {
            $options = explode("/", $options[1]);
            @array_shift($options);

            $o = array();
            for ($j = 0; $j < @count($options); $j++) {
                $options[$j] = preg_replace("#\s+#", " ", trim((string)$options[$j]));
                if (strpos((string)$options[$j], " ") !== false) {
                    $parts = explode(" ", $options[$j]);
                    $o[$parts[0]] = $parts[1];
                } else {
                    $o[$options[$j]] = true;
                }
            }
            $options = $o;
            unset($o);
        }

        return $options;
    }
    function getDecodedStream($stream, $options)
    {
        $data = "";
        if (empty($options["Filter"])) {
            $data = $stream;
        } else {
            $length = !empty($options["Length"]) ? $options["Length"] : strlen((string)$stream);
            $_stream = substr((string)$stream, 0, $length);

            foreach ($options as $key => $value) {
                if ($key == "ASCIIHexDecode") {
                    $_stream = decodeAsciiHex($_stream);
                }
                if ($key == "ASCII85Decode") {
                    $_stream = decodeAscii85($_stream);
                }
                if ($key == "FlateDecode") {
                    $_stream = $this->decodeFlate($_stream);
                }
            }
            $data = $_stream;
        }
        return $data;
    }
    function getDirtyTexts(&$texts, $textContainers)
    {
        for ($j = 0; $j < count($textContainers); $j++) {
            if (preg_match_all("#\[(.*)\]\s*TJ#ismU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, @$parts[1]);
            } elseif (preg_match_all("#Td\s*(\(.*\))\s*Tj#ismU", $textContainers[$j], $parts)) {
                $texts = array_merge($texts, @$parts[1]);
            }
        }
    }
    function getCharTransformations(&$transformations, $stream)
    {
        preg_match_all("#([0-9]+)\s+beginbfchar(.*)endbfchar#ismU", $stream, $chars, PREG_SET_ORDER);
        preg_match_all("#([0-9]+)\s+beginbfrange(.*)endbfrange#ismU", $stream, $ranges, PREG_SET_ORDER);

        for ($j = 0; $j < count($chars); $j++) {
            $count = $chars[$j][1];
            $current = explode("\n", trim((string)$chars[$j][2]));
            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match("#<([0-9a-f]{2,4})>\s+<([0-9a-f]{4,512})>#is", trim((string)$current[$k]), $map)) {
                    $transformations[str_pad($map[1], 4, "0")] = $map[2];
                }
            }
        }
        for ($j = 0; $j < count($ranges); $j++) {
            $count = $ranges[$j][1];
            $current = explode("\n", trim((string)$ranges[$j][2]));
            for ($k = 0; $k < $count && $k < count($current); $k++) {
                if (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+<([0-9a-f]{4})>#is", trim((string)$current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $_from = hexdec($map[3]);

                    for ($m = $from, $n = 0; $m <= $to; $m++, $n++) {
                        $transformations[sprintf("%04X", $m)] = sprintf("%04X", $_from + $n);
                    }
                } elseif (preg_match("#<([0-9a-f]{4})>\s+<([0-9a-f]{4})>\s+\[(.*)\]#ismU", trim((string)$current[$k]), $map)) {
                    $from = hexdec($map[1]);
                    $to = hexdec($map[2]);
                    $parts = preg_split("#\s+#", trim((string)$map[3]));

                    for ($m = $from, $n = 0; $m <= $to && $n < count($parts); $m++, $n++) {
                        $transformations[sprintf("%04X", $m)] = sprintf("%04X", hexdec($parts[$n]));
                    }
                }
            }
        }
    }
    function getTextUsingTransformations($texts, $transformations)
    {
        $document = "";
        for ($i = 0; $i < count($texts); $i++) {
            $isHex = false;
            $isPlain = false;

            $hex = "";
            $plain = "";
            for ($j = 0; $j < strlen((string)$texts[$i]); $j++) {
                $c = $texts[$i][$j];
                switch ($c) {
                    case "<":
                        $hex = "";
                        $isHex = true;
                        break;
                    case ">":
                        $hexs = str_split($hex, 4);
                        for ($k = 0; $k < count($hexs); $k++) {
                            $chex = str_pad($hexs[$k], 4, "0");
                            if (isset($transformations[$chex])) {
                                $chex = $transformations[$chex];
                            }
                            $document .= html_entity_decode("&#x".$chex.";");
                        }
                        $isHex = false;
                        break;
                    case "(":
                        $plain = "";
                        $isPlain = true;
                        break;
                    case ")":
                        $document .= $plain;
                        $isPlain = false;
                        break;
                    case "\\":
                        $c2 = $texts[$i][$j + 1];
                        if (in_array($c2, array("\\", "(", ")"))) {
                            $plain .= $c2;
                        } elseif ($c2 == "n") {
                            $plain .= '\n';
                        } elseif ($c2 == "r") {
                            $plain .= '\r';
                        } elseif ($c2 == "t") {
                            $plain .= '\t';
                        } elseif ($c2 == "b") {
                            $plain .= '\b';
                        } elseif ($c2 == "f") {
                            $plain .= '\f';
                        } elseif ($c2 >= '0' && $c2 <= '9') {
                            $oct = preg_replace("#[^0-9]#", "", substr((string)$texts[$i], $j + 1, 3));
                            $j += strlen((string)$oct) - 1;
                            $plain .= html_entity_decode("&#".octdec($oct).";");
                        }
                            $j++;
                        break;

                    default:
                        if ($isHex) {
                            $hex .= $c;
                        }
                        if ($isPlain) {
                            $plain .= $c;
                        }
                        break;
                }
            }
            $document .= "\n";
        }

        return $document;
    }

    function pdf2text($filename)
    {
        $infile = @file_get_contents($filename, FILE_BINARY);
        if (empty($infile)) {
            return "";
        }


        $transformations = array();
        $texts = array();

        preg_match_all("#obj(.*)endobj#ismU", $infile, $objects);
        $objects = @$objects[1];

        for ($i = 0; $i < count($objects); $i++) {
            $currentObject = $objects[$i];

            if (preg_match("#stream(.*)endstream#ismU", $currentObject, $stream)) {
                $stream = ltrim((string)$stream[1]);

                $options = $this->getObjectOptions($currentObject);
                if (!(empty($options["Length1"]) && empty($options["Type"]) && empty($options["Subtype"]))) {
                    continue;
                }

                $data = $this->getDecodedStream($stream, $options);
                if (strlen((string)$data)) {
                    if (preg_match_all("#BT(.*)ET#ismU", $data, $textContainers)) {
                        $textContainers = @$textContainers[1];
                        $this->getDirtyTexts($texts, $textContainers);
                    } else {
                        $this->getCharTransformations($transformations, $data);
                    }
                }
            }
        }

        return $this->getTextUsingTransformations($texts, $transformations);
    }
}
