<?php

abstract class AddressCodeLang {
    public abstract function getInitialContext();
    public abstract function getHeader();
    public abstract function getCommentSeparator();
    public abstract function getAddressSeparator();
    public abstract function isValidOpcode($opcode);
    public abstract function isValidAddress($addr_type, $addr);
    public abstract function getAddressToken($addr_type, $addr);
}