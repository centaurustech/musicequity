<?php namespace MusicEquity\Helpers;

/**
 * Class Mp3File
 *
 * Fetch mp3 file information
 *
 * relies on libav-tools package
 * @package MusicEquity\Helpers
 */
class Mp3File
{

    /**
     * Mp3 file name with full path
     *
     * @var string
     */
    protected $filename;

    /**
     * mp3 file format
     *
     * @var
     */
    protected $format;

    /**
     * mp3 file info (id3 and etc)
     *
     * @var
     */
    protected $info;



    public function __construct( $filename )
    {
        $this->filename = $filename;
    }

    /**
     * Returns array of mp3 parameters
     *
     * @return array
     */

    public function getFormat()
    {
        $cmd = "ffprobe ". $this->filename ." -show_format 2>/dev/null";
        if(file_exists( $this->filename )) {
            exec($cmd, $output);
        }
        $result = [];
        foreach ($output as $o) {
            $arr = explode('=', $o);
            if(count($arr) == 2) {
                $result[$arr[0]] = $arr[1];
            }
        }
        $this->format = $result;
        return $result;
    }

    function getid3 ()
    {
        if (file_exists($this->filename))
        {
            $id_start=filesize($this->filename)-128;
            $fp=fopen($this->filename,"r");
            fseek($fp,$id_start);
            $tag=fread($fp,3);
            if ($tag == "TAG")
            {
                $this->info['title']=fread($fp,30);
                $this->info['artist']=fread($fp,30);
                $this->info['album']=fread($fp,30);
                $this->info['year']=fread($fp,4);
                $this->info['comment']=fread($fp,30);
                $this->info['genre']=fread($fp,1);
                fclose($fp);
                return $this->info;
            } else
            {
                fclose($fp);
                return false;
            }
        } else { return false; }
    }

    public function getDuration()
    {
        return $this->format['duration'];
    }


    
}