<?php

namespace App\Services;

use App\Repositories\Contracts\VkSentMessageRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VkMessageService
{
    public function __construct(
        private  VkGroupMessageService $vkGroupMessageService,
        private  VkSentMessageRepositoryInterface $vkSentMessageRepository
    ) {
    }

    /**
     * Отправить сообщение от имени группы VK (service token).
     */
    public function sendFromGroup(int|string $vkId, string $message): bool
    {
        $messageId = (string) Str::ulid();

        if(1==1) {
            $this->vkSentMessageRepository->createPending($messageId, $vkId, $message, 'group');

            if (!$this->canSendFromGroup($vkId)) {
                $this->vkSentMessageRepository->markAsFailed($messageId, [
                    'message' => 'User is not eligible for group messages',
                    'vk_id' => (int)$vkId,
                ]);

                return false;
            }
        }

        $result = $this->vkGroupMessageService->sendToUserWithResult($vkId, $message);
        if (($result['success'] ?? false) === true) {
            $this->vkSentMessageRepository->markAsSent(
                $messageId,
                $result['provider_message_id'] ?? null,
                $result['response'] ?? null
            );

            return true;
        }

        $this->vkSentMessageRepository->markAsFailed(
            $messageId,
            $result['error'] ?? null,
            $result['response'] ?? null
        );

        return false;
    }

    /**
     * Проверить, можно ли отправить сообщение пользователю от имени группы.
     */
    public function canSendFromGroup(int|string $vkId): bool
    {
        return $this->vkGroupMessageService->canSendFromGroup($vkId);
    }

    /**
     * Отправить сообщение от имени пользователя (user access token).
     */
    public function sendWithAccessToken(string $accessToken, int|string $vkId, string $message): bool
    {
        $messageId = (string) Str::ulid();
        $this->vkSentMessageRepository->createPending($messageId, $vkId, $message, 'user_token');

        try {
            $response = Http::asForm()->timeout(30)->post('https://api.vk.com/method/messages.send', [
                'access_token' => $accessToken,
                'user_id' => (int) $vkId,
                'message' => trim($message),
                'random_id' => random_int(1, 2_147_483_647),
                'v' => '5.131',
            ]);

            $result = $response->json();

            if (isset($result['error'])) {
                Log::error('VK message error', [
                    'vk_id' => $vkId,
                    'error' => $result['error'],
                ]);

                $this->vkSentMessageRepository->markAsFailed($messageId, $result['error'], $result);

                return false;
            }

            $this->vkSentMessageRepository->markAsSent(
                $messageId,
                isset($result['response']) ? (string) $result['response'] : null,
                $result
            );

            return true;
        } catch (Exception $e) {
            Log::error('VK message exception', [
                'vk_id' => $vkId,
                'message' => $e->getMessage(),
            ]);

            $this->vkSentMessageRepository->markAsFailed($messageId, ['message' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Проверить валидность access token.
     */
    public function checkTokenValidity(string $accessToken): bool
    {
        try {
            $response = Http::timeout(10)->get('https://api.vk.com/method/users.get', [
                'access_token' => $accessToken,
                'v' => '5.131',
            ]);

            $data = $response->json();

            if (isset($data['error'])) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            Log::error('VK token check exception', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Получить информацию профиля для переданного токена.
     *
     * @return array{success: bool, data?: mixed, error?: string, details?: mixed}
     */
    public function getProfileInfo(string $accessToken): array
    {
        try {
            $response = Http::timeout(10)->get('https://api.vk.com/method/account.getProfileInfo', [
                'access_token' => $accessToken,
                'v' => '5.131',
            ]);

            $data = $response->json();

            if (isset($data['error'])) {
                return [
                    'success' => false,
                    'error' => 'VK API error',
                    'details' => $data['error'],
                ];
            }

            return [
                'success' => true,
                'data' => $data['response'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to get token info',
                'details' => $e->getMessage(),
            ];
        }
    }
}
