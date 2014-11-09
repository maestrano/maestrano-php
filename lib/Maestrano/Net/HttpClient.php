<?php
  
class Maestrano_Net_HttpClient
{
  public function get($url) {
    file_get_contents($url);
  }
}
  
?>