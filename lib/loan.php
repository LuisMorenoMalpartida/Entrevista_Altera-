<?php

declare(strict_types=1);

function loan_days_overdue(array $loan, ?DateTimeImmutable $today = null): int
{
    $today = $today ?? new DateTimeImmutable('today');
    if ((float) $loan['saldo_pendiente'] <= 0) {
        return 0;
    }

    $nextPayment = new DateTimeImmutable($loan['proxima_fecha_pago']);
    if ($today <= $nextPayment) {
        return 0;
    }

    return (int) $today->diff($nextPayment)->format('%a');
}

function loan_status(array $loan, ?DateTimeImmutable $today = null): string
{
    $today = $today ?? new DateTimeImmutable('today');
    if ((float) $loan['saldo_pendiente'] <= 0) {
        return 'CANCELADO';
    }

    $nextPayment = new DateTimeImmutable($loan['proxima_fecha_pago']);
    if ($today <= $nextPayment) {
        return 'AL_DIA';
    }

    $days = (int) $today->diff($nextPayment)->format('%a');
    if ($days <= 7) {
        return 'MORA_1_7';
    }
    if ($days <= 30) {
        return 'MORA_8_30';
    }

    return 'MORA_31_MAS';
}
