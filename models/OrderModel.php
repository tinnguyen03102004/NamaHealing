<?php
namespace NamaHealing\Models;

use PDO;

/**
 * Simple model handling VNPay orders to avoid double-payment issues
 * and to record transaction history.
 */
class OrderModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a pending order before redirecting to VNPay.
     */
    public function create(string $txnRef, string $name, string $email, string $phone, int $sessions, int $amount): void {
        $stmt = $this->db->prepare(
            "INSERT INTO orders (txn_ref, full_name, email, phone, sessions, amount, status, created_at)
             VALUES (?,?,?,?,?,?,'pending',NOW())"
        );
        $stmt->execute([$txnRef, $name, $email, $phone, $sessions, $amount]);
    }

    /**
     * Mark order as paid and return order data. If already paid, just return existing row.
     */
    public function markPaid(string $txnRef): ?array {
        $stmt = $this->db->prepare('SELECT * FROM orders WHERE txn_ref = ?');
        $stmt->execute([$txnRef]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            return null;
        }
        if ($order['status'] === 'paid') {
            return $order; // already processed
        }
        $stmt = $this->db->prepare("UPDATE orders SET status='paid', paid_at=NOW() WHERE id = ?");
        $stmt->execute([$order['id']]);
        $order['status'] = 'paid';
        return $order;
    }
}

