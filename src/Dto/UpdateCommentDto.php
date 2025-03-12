<?php

namespace App\Dto;

class UpdateCommentDto
{
    public ?string $status;

    public function getStatus(){
        return $this->status;
    } 

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

}