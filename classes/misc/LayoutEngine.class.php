<?php
/**
 * this class gets all messages from the application and
 * creates a html output as result of getContent()
 * 
 * 
 * This code is licensed under GPL. You can read about the license here:
 * 		http://www.gnu.org/copyleft/gpl.html
 * 
 * More information about this are available at
 * @link http://enarion.net/google/ homepage of phpSitemapNG
 * 
 * @author Tobias Kluge, enarion.it Internet-Service
 * @version 1.0 from 2005-08-12
 */
class LayoutEngine {
	var $content = array();
	var $buffering = true;
	var $static_title = "";
	
    function LayoutEngine($staticTitle) {
    	$this->content[content_header] = "";
    	$this->content[content_footer] = "";
    	$this->content[title] = "";
    	$this->content[charset] = "";
		$this->content[header] = array();
    	$this->content[css] = array();
    	$this->content[body] = array();
    	$this->static_title = $staticTitle;
    }
    
    function addHeader($header) {
    	$this->content[header][] = $header;
    }
    function switchOffBuffer() {
    	$this->buffering = false;
    }

    function addCss($msg) {
    	$this->content[css][] = $msg;
    }
    
    function addContentHeader($msg) {
    	$tmp = '<div class="content_header">'.$msg.'</div>'. "\n";

    	if ($this->buffering) {
    		$this->content[content_header][] = $tmp;
    	} else {
    		print $tmp;
    	}
    }

    function addContentFooter($msg) {
    	$tmp = '<p>'.$msg.'</p>'. "\n";
    	if ($this->buffering) {
    		$this->content[content_footer][] = $tmp;
    	} else {
    		print $tmp;
    	}
    }

    function setTitle($msg) {
    	$this->content[title] = $msg;
    }

    function setCharset($msg) {
    	$this->content[charset] = $msg;
    }
   
    function addError($msg, $title="") {
    	if ($title != "") {
    		$tmp = '<h4 class="error">Error: '.$title.'</h4>'."\n".'<div class="error">'.$msg.'</div>'."\n";
    	} else {
    		$tmp = '<div class="error">Error: '.$msg.'</div>'."\n";
    	}
    	if ($this->buffering) {
    		$this->content[body][] = $tmp;
    	} else {
    		print $tmp;
    	}
    }

    function addWarning($msg, $title = "") {
    	if ($title != "") {
    		$tmp = '<h4 class="warning">Warning: '.$title.'</h4>'."\n".'<div class="warning">'.$msg.'</div>'."\n";
    	} else {
    		$tmp = '<div class="warning">Warning: '.$msg.'</div>'."\n";
    	}
    	if ($this->buffering) {
    		$this->content[body][] = $tmp;
    	} else {
    		print $tmp;
    	}
    }
    	
    function addInfo($msg, $title = "") {
    	if ($title != "") {
    		$tmp = '<h4 class="info">Info: '.$title.'</h4>'."\n".'<div class="info">'.$msg.'</div>'."\n";
    	} else {
    		$tmp = '<div class="info">Info: '.$msg.'</div>'."\n";
    	}
    	if ($this->buffering) {
    		$this->content[body][] = $tmp;
    	} else {
    		print $tmp;
    	}
    	
    }
    
    function addText($msg, $title = "", $css_class="") {
    	if ($css_class != "") {
    		$css_class = ' class="'.$css_class.'"'."\n";
    		if ($title != "") $title = '<h4'.$css_class.'>'.$title.'</h4>'."\n";
	    	$tmp = $title.'<div'.$css_class.'>'.$msg.'</div>'."\n";
    	} else {
    		if ($title != "") $title = '<h4>'.$title.'</h4>'."\n";
    		$tmp = $title.' ' . $msg . " \n";
    	}
    	if ($this->buffering) {
    		$this->content[body][] = $tmp;
    	} else {
    		print $tmp;
    	}
    	
    }
        
    function addDebug($msg, $title = '') {
    	if ($title != "") {
    		$tmp = '<h4 class="debug">Debug: '.$title.'</h4>'."\n".'<div class="debug">'.$msg.'</div>'."\n";
    	} else {
    		$tmp = '<div class="debug">Debug: '.$msg.'</div>'."\n";
    	}
    	if ($this->buffering) {
    		$this->content[body][] = $tmp;
    	} else {
    		print $tmp;
    	}
    	
    }

    function addSuccess($msg, $title = '') {
    	if ($title != "") {
    		$tmp = '<h4 class="success">Successful: '.$title.'</h4>'."\n".'<div class="success">'.$msg.'</div>'."\n";
    	} else {
    		$tmp = '<div class="success">Successful: '.$msg.'</div>'."\n";
    	}
    	if ($this->buffering) {
    		$this->content[body][] = $tmp;
    	} else {
    		print $tmp;
    	}    	
    }
    
    
    function getFooterLayout() {
    	if ($this->buffering) return '';
    	$res = '';
		if(($this->content[content_footer] != "") && count($this->content[content_footer]) > 0) {		
	    	foreach ($this->content[content_footer] as $line) {
	    		$res .= '<div class="content_footer">'.$line.'</div>'. "\n";
	    	}
		}
	    	
    	$res .= "</body>";
    	$res .= "</html>";
    	return $res;
    }

    function getHeaderLayout() {
    	if ($this->buffering) return '';
    	$res = '<html><head>'."\n";
		$res .= '<meta http-equiv="Content-Type" content="text/html; charset='.$this->content[charset].'">'."\n";
    	$res .= '<title>'.$this->static_title .' ' . $this->content[title].'</title>'."\n";
    	// header
    	if(($this->content[header] != "") && count($this->content[header]) > 0) {
    		foreach ($this->content[header] as $head) {
    			$res .= $head . "\n";
    		}
    	}
    	// css
    	$res .= '<style type="text/css">'."\n".'<!--'."\n";
    	if(($this->content[css] != "") && count($this->content[css]) > 0) {
	    	foreach ($this->content[css] as $line) {
    			$res .= $line . "\n";
    		}
    	}
    	$res .= '-->'."\n".'</style>'."\n";
		
		//end of head
		$res .= '</head><body>'."\n";
		 
		$res .= '<h1>'.$this->content[title].'</h1>';
		
		if(($this->content[content_header] != "") && count($this->content[content_header])>0) {		
	    	foreach ($this->content[content_header] as $line) {
	    		$res .= $line . "\n";
	    	}
		}
    	return $res;
    }
    
    function getContent() {
    	if (! $this->buffering) {
	    	$res = '<html><head>'."\n";
			$res .= '<meta http-equiv="Content-Type" content="text/html; charset='.$this->content[charset].'">'."\n";
	    	$res .= '<title>'.$this->static_title .' ' . $this->content[title].'</title>'."\n";
	    	// header
	    	if(($this->content[header] != "") && count($this->content[header]) > 0) {
	    		foreach ($this->content[header] as $head) {
	    			$res .= $head . "\n";
	    		}
	    	}
	    	// css
	    	$res .= '<style type="text/css">'."\n".'<!--'."\n";
	    	if(($this->content[css] != "") && count($this->content[css]) > 0) {
		    	foreach ($this->content[css] as $line) {
	    			$res .= $line . "\n";
	    		}
	    	}
	    	$res .= '-->'."\n".'</style>'."\n";
			
			//end of head
			$res .= '</head><body>'."\n";
			 
			$res .= '<h1>'.$this->content[title].'</h1>';
			
			if(($this->content[content_header] != "") && count($this->content[content_header])>0) {		
		    	foreach ($this->content[content_header] as $line) {
		    		$res .= $line . "\n";
		    	}
			}
			
			if(($this->content[body] != "") && count($this->content[body]) > 0) {		
		    	foreach ($this->content[body] as $line) {
		    		$res .= $line . "\n";
		    	}
			}
			
			if(($this->content[content_footer] != "") && count($this->content[content_footer]) > 0) {		
		    	foreach ($this->content[content_footer] as $line) {
		    		$res .= '<div class="content_footer">'.$line.'</div>'. "\n";
		    	}
			}
	    	
	    	$res .= "</body>";
	    	$res .= "</html>";
    	} else {
    		$res = '';
    	}    	
    	return $res;
    }    
}
?>