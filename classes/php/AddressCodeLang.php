<?php

abstract class AddressCodeLang {
    public abstract function isValidOpcode($opcode);
    public abstract function getHeader();
    public abstract function getCommentSeparator();
}