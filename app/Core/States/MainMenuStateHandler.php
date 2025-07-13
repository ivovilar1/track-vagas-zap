<?php

namespace App\Core\States;

use App\Core\States\Interface\StateHandlerInterface;
use App\Enums\ConversationStateEnum;
use App\Models\User;
use App\Services\Whatsapp\EvolutionApiService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MainMenuStateHandler extends BaseStateHandler implements StateHandlerInterface
{
    public function __construct(
        private readonly EvolutionApiService $evolutionApiService,
    ) {}

    public function handle(User $user, string $message): void
    {
        $messageLower = Str::lower($message);

        if (in_array($messageLower, ['cancelar', 'menu'])) {
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_handle_cancel'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $user->update([
                'conversation_state' => ConversationStateEnum::MAIN_MENU,
                'context' => [],
            ]);

            return;
        }

        $option = trim($message);

        switch ($option) {
            case '1':
                $this->sendApplicationList($user);
                break;
            case '2':
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_create_start'));
                $this->updateConversationState($user, ConversationStateEnum::APPLICATION_CREATE);
                break;
            case '3':
                $this->sendApplicationList(
                    $user,
                    ConversationStateEnum::APPLICATION_DELETE,
                    'bot_messages.application_list_delete_prompt'
                );
                break;
            case '4':
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_end_conversation'));
                $this->updateConversationState($user, ConversationStateEnum::IDLE);
                break;
            default:
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.invalid_option'));
                $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
                $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);
                break;
        }
    }

    private function sendApplicationList(
        User $user,
        ConversationStateEnum $nextState = ConversationStateEnum::APPLICATION_LIST,
        string $promptMessageKey = 'bot_messages.application_list_prompt'
    ): void {
        $applications = $user->applications()->latest()->get();

        if ($applications->isEmpty()) {
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_list_empty'));
            $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.main_menu'));
            $this->updateConversationState($user, ConversationStateEnum::MAIN_MENU);

            return;
        }

        $applicationListFormatted = [];
        $applicationIds = [];
        foreach ($applications as $index => $application) {
            $data = [
                'index' => $index + 1,
                'job_title' => $application->job_title,
                'company_name' => $application->company_name ?? 'N/A',
                'job_description' => $application->job_description ?? 'N/A',
                'job_salary' => $application->job_salary ? 'R$ '.number_format($application->job_salary, 2, ',', '.') : 'N/A',
                'job_link' => $application->job_link ?? 'N/A',
                'application_date' => Carbon::parse($application->application_date)->format('d/m/Y'),
            ];

            $applicationListFormatted[] = __('bot_messages.application_list_item_details', $data);
            $applicationIds[$index + 1] = $application->id;
        }

        $this->evolutionApiService->sendTextMessage($user->phone, __('bot_messages.application_list_header'));
        $this->evolutionApiService->sendTextMessage($user->phone, implode("\n\n", $applicationListFormatted));
        $this->evolutionApiService->sendTextMessage($user->phone, __($promptMessageKey));

        $user->update(['context' => ['application_ids' => $applicationIds]]);
        $this->updateConversationState($user, $nextState);
    }
}
