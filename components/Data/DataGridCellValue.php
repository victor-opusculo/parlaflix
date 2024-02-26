<?php
namespace VictorOpusculo\Parlaflix\Components\Data;

interface DataGridCellValue
{
    public function generateHtml() : string;
}