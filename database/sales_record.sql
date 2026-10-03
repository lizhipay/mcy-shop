-- 销售登记表
CREATE TABLE IF NOT EXISTS `sales_record` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `salesman` varchar(50) NOT NULL COMMENT '销售人名称',
  `product_name` varchar(100) NOT NULL COMMENT '商品名称',
  `order_amount` decimal(14,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '销售金额',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态:1有效,0无效',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_create_time` (`create_time`),
  KEY `idx_salesman` (`salesman`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='销售登记表';
