<?php namespace MusicEquity\Helpers;


/**
 * Class Waveform
 * Author: Alex Lisowski ( alex@netcomp.com.au )
 *
 * Simple class to generate mp3 waveform
 *
 * Original idea : https://github.com/afreiday/php-waveform-png/blob/master/php-waveform-png.php
 *
 * All credits to Andrew  ( andrewfreiday@gmail.com )
 *
 * @package MusicEquity\Helpers
 */

class Waveform {

    /**
     * How much detail we want. The lower number means more detail.
     *
     * @var string
     */
    public $detail = 35;

    /**
     * Generated image width
     *
     * @var integer
     */
    public $width = 470;

    /**
     * Generated image height
     *
     * @var integer
     */
    public $height = 90;

    /**
     * Foreground color
     *
     * @var string
     */
    public $foreground = "#607BA9";

    /**
     * Background color
     *
     * @var string
     */
    public $background = "#FFFFFF";

    /**
     * File to process
     *
     * @var string
     */

    protected $filename;

    /**
     * Temporary file name
     *
     * @var string
     */

    protected $tempname;

    /**
     * Wav files to process
     *
     * @var array
     */
    protected $process;

    public function __construct( $filename )
    {
        $this->tempname = storage_path() ."/temp/" . substr(md5(microtime()), 0, 6);

        $this->filename = $filename;

        copy($filename, $this->tempname . "_o.mp3" );


    }

    /**
     * Generating waveform
     *
     * @return string
     */
    public function wave()
    {

        $this->encode();
        list($r, $g, $b) = $this->html2rgb($this->foreground);
        $img = false;
        for($wav = 1; $wav <= sizeof($this->process); $wav++) {

            $filename = $this->process[$wav - 1];

            /**
             * Below as posted by "zvoneM" on
             * http://forums.devshed.com/php-development-5/reading-16-bit-wav-file-318740.html
             * as findValues() defined above
             * Translated from Croation to English - July 11, 2011
             */
            $handle = fopen($filename, "r");
            // wav file header retrieval
            $heading[] = fread($handle, 4);
            $heading[] = bin2hex(fread($handle, 4));
            $heading[] = fread($handle, 4);
            $heading[] = fread($handle, 4);
            $heading[] = bin2hex(fread($handle, 4));
            $heading[] = bin2hex(fread($handle, 2));
            $heading[] = bin2hex(fread($handle, 2));
            $heading[] = bin2hex(fread($handle, 4));
            $heading[] = bin2hex(fread($handle, 4));
            $heading[] = bin2hex(fread($handle, 2));
            $heading[] = bin2hex(fread($handle, 2));
            $heading[] = fread($handle, 4);
            $heading[] = bin2hex(fread($handle, 4));

            // wav bitrate
            $peek = hexdec(substr($heading[10], 0, 2));
            $byte = $peek / 8;

            // checking whether a mono or stereo wav
            $channel = hexdec(substr($heading[6], 0, 2));

            $ratio = ($channel == 2 ? 40 : 80);

            // start putting together the initial canvas
            // $data_size = (size_of_file - header_bytes_read) / skipped_bytes + 1
            $data_size = floor((filesize($filename) - 44) / ($ratio + $byte) + 1);
            $data_point = 0;

            // now that we have the data_size for a single channel (they both will be the same)
            // we can initialize our image canvas
            if (!$img) {
                // create original image width based on amount of detail
                // each waveform to be processed with be $height high, but will be condensed
                // and resized later (if specified)
                $img = imagecreatetruecolor($data_size / $this->detail, $this->height * sizeof($this->process));

                // fill background of image
                if ($this->background == "") {
                    // transparent background specified
                    imagesavealpha($img, true);
                    $transparentColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
                    imagefill($img, 0, 0, $transparentColor);
                } else {
                    list($br, $bg, $bb) = $this->html2rgb($this->background);
                    imagefilledrectangle($img, 0, 0, (int) ($data_size / $this->detail), $this->height * sizeof($this->process), imagecolorallocate($img, $br, $bg, $bb));
                }
            }

            while(!feof($handle) && $data_point < $data_size){
                if ($data_point++ % $this->detail == 0) {
                    $bytes = array();

                    // get number of bytes depending on bitrate
                    for ($i = 0; $i < $byte; $i++)
                        $bytes[$i] = fgetc($handle);

                    switch($byte){
                        // get value for 8-bit wav
                        case 1:
                            $data = $this->findValues($bytes[0], $bytes[1]);
                            break;
                        // get value for 16-bit wav
                        case 2:
                            if(ord($bytes[1]) & 128)
                                $temp = 0;
                            else
                                $temp = 128;
                            $temp = chr((ord($bytes[1]) & 127) + $temp);
                            $data = floor($this->findValues($bytes[0], $temp) / 256);
                            break;
                    }

                    // skip bytes for memory optimization
                    fseek($handle, $ratio, SEEK_CUR);

                    // draw this data point
                    // relative value based on height of image being generated
                    // data values can range between 0 and 255
                    $v = (int) ($data / 255 * $this->height);

                    // don't print flat values on the canvas if not necessary
                    //!($v / $this->height == 0.5)
                    $newcolor = imagecolorallocate($img, $r,$g,$b);
                    if (true)
                        // draw the line on the image using the $v value and centering it vertically on the canvas
                        imageline(
                            $img,
                            // x1
                            (int) ($data_point / $this->detail),
                            // y1: height of the image minus $v as a percentage of the height for the wave amplitude
                            $this->height * $wav - $v,
                            // x2
                            (int) ($data_point / $this->detail),
                            // y2: same as y1, but from the bottom of the image
                            $this->height * $wav - ($this->height - $v),

                            imagecolorallocate($img, $r, $g, $b)
                        );

                } else {
                    // skip this one due to lack of detail
                    fseek($handle, $ratio + $byte, SEEK_CUR);
                }

            }
            imagecolortransparent($img,$newcolor);
            $final_img[$wav] = $img;
            // close and cleanup
            fclose($handle);

            // delete the processed wav file
            unlink($filename);

        }


        // resample the image to the proportions defined in the form
        $rimg = imagecreatetruecolor($this->width, $this->height);
        // save alpha from original image
        imagesavealpha($rimg, true);
        imagealphablending($rimg, false);
        // copy to resized
        imagecopyresampled($rimg, $final_img[1], 0, 0, 0, 0, $this->width, $this->height, imagesx($final_img[1]), imagesy($final_img[1]));




        imagepng($rimg,  $this->filename .".png");
        imagedestroy($rimg);
        return true;

    }

    /**
     * Encode mp3 to wav, return path/file/name
     *
     * @return string
     */

    protected function encode()
    {

        $cmd = "lame ". $this->tempname ."_o.mp3 -m m -S -f -b 16 --resample 8 ". $this->tempname .".mp3 && lame -S --decode ". $this->tempname .".mp3 ". $this->tempname .".wav";
        exec($cmd);
        $this->process[] = $this->tempname.".wav";
        unlink($this->tempname ."_o.mp3");
        unlink($this->tempname .".mp3");
        return true;
    }

    /**
     * Encoding HEX to RGB
     *
     * @return array
     */

    protected function html2rgb($input) {
        $input=($input[0]=="#")?substr($input, 1,6):substr($input, 0,6);
        return [
             hexdec(substr($input, 0, 2)),
             hexdec(substr($input, 2, 2)),
             hexdec(substr($input, 4, 2))
        ];
    }

    protected function findValues($byte1, $byte2){
        $byte1 = hexdec(bin2hex($byte1));
        $byte2 = hexdec(bin2hex($byte2));
        return ($byte1 + ($byte2*256));
    }



}