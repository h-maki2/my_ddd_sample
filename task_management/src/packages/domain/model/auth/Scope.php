<?php

namespace packages\domain\model\auth;

enum Scope: string
{
    case ReadAccount = 'read_account';
    case EditAccount = 'edit_account';
    case DeleteAccount = 'delete_account';
}