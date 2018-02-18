<?php

abstract class AddressCodeLang {
    public abstract function getInitialContext();
    public abstract function getHeader();
    public abstract function getCommentSeparator();
    public abstract function getAddressSeparator();
    public abstract function isValidOpcode($opcode);
    public abstract function isValidAddress($addrType, $addr);
    public abstract function getAddressToken($addrType, $addr);
    public abstract function splitInstruction($instruction);
}