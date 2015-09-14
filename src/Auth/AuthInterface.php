<?php
/**
 * AuthInterface
 *
 * Created 9/14/15 2:35 PM
 * Interface for send and receive classes
 *
 * @author Nate Nolting <me@natenolting.com>
 * @package Pbc\Api
 * @subpackage Auth
 */
namespace Pbc\Api\Auth;

interface AuthInterface
{
    public function generateHash(array $list = []);
}