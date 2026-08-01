<?php


namespace Nyos\Vk;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VkGroupMessageService
{
    public function canSendFromGroup(int|string $vkId): bool
    {
        $token = (string) config('services.vk.service_token');
        $groupId = (int) config('services.vk.group_id');

        if ($token === '' || $groupId <= 0) {
            Log::warning('VK group access check config is missing', [
                'has_token' => $token !== '',
                'group_id' => $groupId,
            ]);

            return false;
        }

        $isMember = $this->isGroupMember($token, $groupId, (int) $vkId);
        $isAllowed = $this->isMessagesFromGroupAllowed($token, $groupId, (int) $vkId);

        return $isMember && $isAllowed;
    }

    /**
     * Отправка сообщения пользователю от имени группы VK.
     */
    public function sendToUser(int|string $vkId, string $message): bool
    {
        return $this->sendToUserWithResult($vkId, $message)['success'];
    }

    /**
     * Отправка сообщения пользователю от имени группы VK с детальным результатом.
     *
     * @return array{success: bool, response?: array, error?: array, provider_message_id?: null|string}
     */
    public function sendToUserWithResult(int|string $vkId, string $message): array
    {
        $token = (string) config('services.vk.order_token');

        if ($token === '') {
            Log::warning('VK service token is missing');
            return [
                'success' => false,
                'error' => ['message' => 'VK service token is missing'],
            ];
        }

        try {
            $response = Http::asForm()->timeout(30)->post('https://api.vk.com/method/messages.send', [
                'access_token' => $token,
                'user_id' => (int) $vkId,
                'message' => trim($message),
                'random_id' => random_int(1, 2147483647),
                'v' => '5.131',
            ]);

            $result = $response->json();

            if (isset($result['error'])) {
                Log::error('VK group message error', [
                    'vk_id' => $vkId,
                    'error' => $result['error'],
                ]);

                return [
                    'success' => false,
                    'response' => $result,
                    'error' => $result['error'],
                ];
            }

            Log::info('VK group message sent', [
                'vk_id' => $vkId,
                'message_id' => $result['response'] ?? null,
            ]);

            return [
                'success' => true,
                'response' => $result,
                'provider_message_id' => isset($result['response']) ? (string) $result['response'] : null,
            ];
        } catch (Exception $e) {
            Log::error('VK group message exception', [
                'vk_id' => $vkId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => ['message' => $e->getMessage()],
            ];
        }
    }

    private function isGroupMember(string $token, int $groupId, int $vkId): bool
    {
        try {
            $response = Http::timeout(10)->get('https://api.vk.com/method/groups.isMember', [
                'access_token' => $token,
                'group_id' => $groupId,
                'user_id' => $vkId,
                'v' => '5.131',
            ]);

            $result = $response->json();
            if (isset($result['error'])) {
                Log::warning('VK groups.isMember error', ['error' => $result['error'], 'vk_id' => $vkId]);
                return false;
            }

            return (int) ($result['response'] ?? 0) === 1;
        } catch (Exception $e) {
            Log::warning('VK groups.isMember exception', [
                'vk_id' => $vkId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function isMessagesFromGroupAllowed(string $token, int $groupId, int $vkId): bool
    {
        try {
            $response = Http::timeout(10)->get('https://api.vk.com/method/messages.isMessagesFromGroupAllowed', [
                'access_token' => $token,
                'group_id' => $groupId,
                'user_id' => $vkId,
                'v' => '5.131',
            ]);

            $result = $response->json();
            if (isset($result['error'])) {
                Log::warning('VK messages.isMessagesFromGroupAllowed error', [
                    'error' => $result['error'],
                    'vk_id' => $vkId,
                ]);
                return false;
            }

            return (int) ($result['response']['is_allowed'] ?? 0) === 1;
        } catch (Exception $e) {
            Log::warning('VK messages.isMessagesFromGroupAllowed exception', [
                'vk_id' => $vkId,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
