<?php

namespace App\Services;

class  BlockChainService
{
    public function calculateHash($previousHash, $data, $timestamp)
    {
        return hash('sha256', $previousHash . $data . $timestamp);
    }

    public function getPreviousHash(string $model)
    {
        $lastCertificate = $model::orderBy('id', 'desc')->first();
        return $lastCertificate ? $lastCertificate->hash : '0000000000000000000000000000000000000000000000000000000000000000';
    }

}

