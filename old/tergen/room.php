<?php
class room {
      var $xpos;
      var $ypos;
      var $xlen;
      var $ylen;
      var $top;
      var $bot;
      var $lft;
      var $rgt;

    function in_room($x, $y) {
      if ($y >= $this->top && $y <= $this->bot
               && $x >= $this->lft && $x <= $this->rgt) {
        return true;
      } else {
        return false;
      }
    }

    function room($maxx, $minx, $maxy, $miny) {
      srand();
      $x = rand($minx,$maxx);
      $hx = floor($x/2);
      $y = rand($miny,$maxy);
      $hy = floor($y/2);

      $this->xpos = rand(1, 80) - 1;
      $this->ypos = rand(1, 80) - 1;
      $this->xlen = $x;
      $this->ylen = $y;

      $this->top = $this->ypos - $hy;
      if ($this->top < 0) {
        $this->top = 0;
      }
      
      $this->bot = $this->ypos + $hy;
      if ($this->bot > 79) {
        $this->bot = 9;
      }

      $this->lft = $this->xpos - $hx;
      if ($this->lft < 0) {
        $this->lft = 0;
      }
      
      $this->rgt = $this->xpos + $hx;
      if ($this->rgt > 79) {
        $this->rgt = 9;
      }
    }
}
?>
