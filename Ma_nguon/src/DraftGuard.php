<?php
declare(strict_types=1);

function canComplete(array $session, int $eventId, int $now, int $ttl): bool {
    // 1. Kiểm tra invalid-ttl và eventId
    if ($ttl <= 0 || $eventId <= 0) {
        return false;
    }

    // 2. Kiểm tra missing (bản nháp tồn tại và đúng cấu trúc cơ bản)
    if (!isset($session['draft']) || !is_array($session['draft'])) {
        return false;
    }

    $draft = $session['draft'];
    if (!isset($draft['data'], $draft['created_at']) || !is_array($draft['data']) || !is_numeric($draft['created_at'])) {
        return false;
    }

    $data = $draft['data'];
    $createdAt = (int)$draft['created_at'];

    // 3. Kiểm tra wrong-event (có event_id và khớp với $eventId yêu cầu)
    if (!isset($data['event_id']) || (int)$data['event_id'] !== $eventId) {
        return false;
    }

    // Nếu trong data có truyền name hoặc email thì không được rỗng
    if (isset($data['name']) && (!is_string($data['name']) || trim($data['name']) === '')) {
        return false;
    }
    if (isset($data['email']) && (!is_string($data['email']) || trim($data['email']) === '')) {
        return false;
    }

    // 4. Kiểm tra future-created-at (thời điểm tạo không ở tương lai)
    if ($createdAt > $now) {
        return false;
    }

    // 5. Kiểm tra at-expiry & before-expiry (đúng ngưỡng hết hạn phải trả false)
    if ($now - $createdAt >= $ttl) {
        return false;
    }

    return true;
}
