ALTER TABLE pedidos
    MODIFY metodo_pago ENUM('en_libreria', 'transferencia_bancaria', 'handy') NOT NULL,
    ADD COLUMN handy_transaction_external_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NULL UNIQUE,
    ADD COLUMN handy_payment_url VARCHAR(2048) NULL,
    ADD COLUMN handy_payment_status ENUM('not_applicable', 'pending', 'paid', 'failed')
        NOT NULL DEFAULT 'not_applicable';
