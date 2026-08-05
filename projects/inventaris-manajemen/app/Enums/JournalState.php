<?php

namespace App\Enums;

enum JournalState: string
{
    case DRAFT = 'DRAFT';
    case READY = 'READY';
    case SENDING = 'SENDING';
    case SUCCESS = 'SUCCESS';
    case FAILED = 'FAILED';
    case RETRY = 'RETRY';
    case COMPLETED = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::READY => 'Siap Kirim',
            self::SENDING => 'Mengirim...',
            self::SUCCESS => 'Berhasil',
            self::FAILED => 'Gagal',
            self::RETRY => 'Mengulang...',
            self::COMPLETED => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::READY => 'blue',
            self::SENDING => 'indigo',
            self::SUCCESS => 'green',
            self::FAILED => 'red',
            self::RETRY => 'orange',
            self::COMPLETED => 'emerald',
        };
    }

    /**
     * Allowed transitions from this state.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::READY, self::SENDING],
            self::READY => [self::SENDING],
            self::SENDING => [self::SUCCESS, self::FAILED],
            self::SUCCESS => [self::COMPLETED],
            self::FAILED => [self::RETRY, self::SENDING],
            self::RETRY => [self::SENDING],
            self::COMPLETED => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }
}
