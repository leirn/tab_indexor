<?php

//http://dguitar.sourceforge.net/GP4format.html#Information_About_the_Piece

class GPFile {

	public $fileVersion;
	public $version;
	public $title;
	public $subtitle;
	public $interpret;
	public $album;
	public $author;
	public $copyright;
	public $fileauthor;
	public $fileinfo;
	public $notice;
	
	public $lyrics_tracknumber;
	public $lyrics_line1;
	public $lyrics_line2;
	public $lyrics_line3;
	public $lyrics_line4;
	public $lyrics_line5;
	
	public $tempo;
	public $key;
	public $measure_number;
	public $track_number;
	
	private $handle;

	public function __construct($filepath) {
		$this->title 		= "";
		$this->subtitle 	= "";
		$this->interpret 	= "";
		$this->album 		= "";
		$this->author 		= "";
		$this->copyright 	= "";
		$this->fileauthor 	= "";
		$this->fileinfo 	= "";
		$this->notice 		= "";
		$this->triplefeel	= "";
		$this->tempo		= 0;
		$this->key			= 0;
		$this->filepath 	= $filepath;
		
		$this->handle = fopen($this->filepath, 'r');
		
		$this->readMetaData();
		return;
		
		if($this->version == 4)
			$this->readLyrics();
		$this->readScoreInfo();
		
		fclose($this->handle);
	}
	
	private function readScoreInfo() {
		$this->tempo = $this->readInteger();
		$this->key = $this->readByte();
		
		$this->readByte(); // future octave
		for($i = 0; $i <64; $i++) {
			$this->readInteger();
			$this->readByte();
			$this->readByte();
			$this->readByte();
			$this->readByte();
			$this->readByte();
			$this->readByte();
			$this->readByte();
			$this->readByte();
		}
		
		$this->measure_number = $this->readInteger();
		$this->track_number = $this->readInteger();
	}
	
	private function readLyrics() {
		echo "Start reading lyrics".PHP_EOL;
		
		$this->lyrics_tracknumber = $this->readInteger();
		
		$this->lyrics_line1 = $this->readLyricLine();
		$this->lyrics_line2 = $this->readLyricLine();
		$this->lyrics_line3 = $this->readLyricLine();
		$this->lyrics_line4 = $this->readLyricLine();
		$this->lyrics_line5 = $this->readLyricLine();
	}

	private function readLyricLine() {
		$str="";$len = 0;
		$len = $this->readInteger();
		echo "Lyric String len : $len".PHP_EOL;
		if($len == 0) return "";
		$str = fread($this->handle, $len);
		return $str;
	}

	private function readMetaData() {

		// 1 char : longueur de l'identifiant de version
		// 30 char : numéro de version
		$str="";$len = 0;
		$len = $this->readByte();
		$this->fileVersion = substr(fread($this->handle, 30), 0, $len);
		
		$this->version = intval(substr($this->fileVersion, 20, 1));
		echo "Version is ".$this->version.PHP_EOL;
		
	/*
	The information concerned is, in order of reading within the file:
		- The title of the piece;
		- The subtitle of the piece;
		- The interpret of the piece;
		- The album from which the piece was taken;
		- The author of the piece;
		- The copyright;
		- The name of the author of the tablature;
		- An 'instructional' line about the tablature.
	*/
		$this->title 		= $this->readString();
		$this->subtitle 	= $this->readString();
		$this->interpret 	= $this->readString();
		$this->album 		= $this->readString();
		$this->author 		= $this->readString();
		$this->copyright 	= $this->readString();
		$this->fileauthor 	= $this->readString();
		$this->fileinfo 	= $this->readString();

		// Read notice
		$notice_lines = $this->readInteger();
		for($i = 0; $i < $notice_lines; $i++) {
			$this->notice .= $this->readString().PHP_EOL;
		}

		$this->triplefeel	= $this->readByte();
	}
	
	private function readString() {
		$str="";$len = 0;
		$len = $this->readInteger();
		$this->readByte();
		if($len == 1) return "";
		echo "String len : $len".PHP_EOL;
		$str = fread($this->handle, $len-1);
		return $str;
	}
	
	private function readInteger() {
		return ord(fread($this->handle, 1)) + 255 * ord(fread($this->handle, 1)) + 255 * 255 *ord(fread($this->handle, 1)) + 255 * 255 * 255 * ord(fread($this->handle, 1));
	}
	
	private function readShortInteger() {
		return ord(fread($this->handle, 1)) + 255 * ord(fread($this->handle, 1));
	}
	
	private function readByte() {	
		return ord(fread($this->handle, 1));
	}
}

$test1 = new GPFile("test.gp3");
print_r($test1);
/*$test2 = new GPFile("test.gp4");
print_r($test2);*/
?>