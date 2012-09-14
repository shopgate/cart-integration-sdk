<?php
$this->buffer[] = $itemArr;

if (count($this->buffer) > $this->bufferLimit) {
	$this->flush();
}