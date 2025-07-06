<?php

namespace App\Enums;

enum ConversationStateEnum: string
{
    case IDLE = 'idle';
    case MAIN_MENU = 'main_menu';
    case APPLICATION_LIST = 'application_list';
    case APPLICATION_CREATE = 'application_create';
    case APPLICATION_UPDATE = 'application_update';
    case APPLICATION_DELETE = 'application_delete';
}
