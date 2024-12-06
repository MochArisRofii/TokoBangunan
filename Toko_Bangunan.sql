/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toko_bangunan`
--

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `IdKategori` int NOT NULL,
  `NamaKategori` varchar(100) NOT NULL,
  `DeskripsiKategori` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`IdKategori`, `NamaKategori`, `DeskripsiKategori`) VALUES
(1, 'Bahan Berat', NULL),
(2, 'Bahan Ringan', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `laporan_penjualan`
--

CREATE TABLE `laporan_penjualan` (
  `IdLaporan` int NOT NULL,
  `TanggalMulai` date NOT NULL,
  `TanggalAkhir` date NOT NULL,
  `TotalPenjualan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `ProdukTerlaris` varchar(100) DEFAULT NULL,
  `ProdukKurangLaku` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `laporan_penjualan`
--

INSERT INTO `laporan_penjualan` (`IdLaporan`, `TanggalMulai`, `TanggalAkhir`, `TotalPenjualan`, `ProdukTerlaris`, `ProdukKurangLaku`) VALUES
	(3, '2024-11-17', '2024-11-17', 0.00, NULL, NULL),
	(4, '2024-10-17', '2024-11-17', 0.00, 'Cat Tembok', 'Cat Tembok'),
	(5, '2024-11-18', '2024-11-18', 0.00, NULL, NULL),
	(6, '2024-10-18', '2024-11-18', 0.00, 'Semen', 'Semen'),
	(7, '2024-11-18', '2024-11-18', 0.00, NULL, NULL),
	(8, '2024-10-18', '2024-11-18', 0.00, 'Pasir', 'Pasir');

CREATE TABLE `produk` (
  `IdProduk` int NOT NULL,
  `NamaProduk` varchar(100) NOT NULL,
  `Deskripsi` text,
  `Harga` decimal(10,2) NOT NULL,
  `Stok` int DEFAULT '0',
  `IdKategori` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`IdProduk`, `NamaProduk`, `Deskripsi`, `Harga`, `Stok`, `IdKategori`) VALUES
	(1, 'Semen ', '', 50000.00, 98, 1),
	(2, 'Cat Tembok', NULL, 75000.00, 46, 2),
	(3, 'Pasir', NULL, 30000.00, 180, 2),
	(4, 'Batu Bata', NULL, 2000.00, 500, 1),
	(5, 'Ceker Bangunan', '', 60000.00, 400, 2);

CREATE TABLE `transaksi` (
  `IdTransaksi` int NOT NULL,
  `IdUser` int DEFAULT NULL,
  `Tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `TotalBayar` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`IdTransaksi`, `IdUser`, `Tanggal`, `TotalBayar`) VALUES
	(2, 1, '2024-11-18 02:30:42', 50000.00),
	(3, 1, '2024-11-18 02:31:56', 800000.00);

CREATE TABLE IF NOT EXISTS `transaksi_item` (
  `IdItem` int NOT NULL AUTO_INCREMENT,
  `IdTransaksi` int DEFAULT NULL,
  `IdProduk` int DEFAULT NULL,
  `Jumlah` int NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`IdItem`),
  KEY `IdTransaksi` (`IdTransaksi`),
  KEY `IdProduk` (`IdProduk`),
  CONSTRAINT `transaksi_item_ibfk_1` FOREIGN KEY (`IdTransaksi`) REFERENCES `transaksi` (`IdTransaksi`) ON DELETE CASCADE,
  CONSTRAINT `transaksi_item_ibfk_2` FOREIGN KEY (`IdProduk`) REFERENCES `produk` (`IdProduk`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `transaksi_item` (`IdItem`, `IdTransaksi`, `IdProduk`, `Jumlah`, `Subtotal`) VALUES
	(2, 2, 1, 1, 50000.00),
	(3, 3, 1, 1, 50000.00),
	(4, 3, 2, 2, 150000.00),
	(5, 3, 3, 20, 600000.00);

CREATE TABLE IF NOT EXISTS `users` (
  `IdUser` int NOT NULL AUTO_INCREMENT,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `IdLevel` int NOT NULL,
  PRIMARY KEY (`IdUser`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`IdUser`, `Username`, `Password`, `Nama`, `IdLevel`) VALUES
	(1, 'kasir', 'kasir', 'Kasir Bangunan', 1);

SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE DEFINER=`root`@`localhost` TRIGGER `update_laporan_penjualan` AFTER INSERT ON `transaksi` FOR EACH ROW BEGIN
    DECLARE total_penjualan DECIMAL(15, 2) DEFAULT 0;
    DECLARE start_date DATE;
    DECLARE end_date DATE;

    
    SELECT IFNULL(SUM(Subtotal), 0) INTO total_penjualan
    FROM transaksi_item
    WHERE IdTransaksi = NEW.IdTransaksi;

    
    SET start_date = CURDATE();
    SET end_date = CURDATE();

    
    INSERT INTO laporan_penjualan (TanggalMulai, TanggalAkhir, TotalPenjualan)
    VALUES (start_date, end_date, total_penjualan);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_laporan_ringkas` AFTER INSERT ON `transaksi` FOR EACH ROW BEGIN
    DECLARE total_penghasilan DECIMAL(15, 2) DEFAULT 0;
    DECLARE jumlah_transaksi INT DEFAULT 0;

    
    SELECT IFNULL(SUM(TotalBayar), 0), COUNT(IdTransaksi)
    INTO total_penghasilan, jumlah_transaksi
    FROM transaksi
    WHERE Tanggal BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE();

    
    INSERT INTO laporan_penjualan (TanggalMulai, TanggalAkhir, TotalPenjualan)
    VALUES (DATE_SUB(CURDATE(), INTERVAL 1 MONTH), CURDATE(), total_penghasilan);

    
    UPDATE laporan_penjualan
    SET TotalPenjualan = total_penghasilan
    WHERE IdLaporan = (
        SELECT IdLaporan FROM (
            SELECT MAX(IdLaporan) AS IdLaporan FROM laporan_penjualan
        ) AS temp_table
    );
END
$$
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE DEFINER=`root`@`localhost` TRIGGER `update_produk_kurang_terjual` AFTER INSERT ON `transaksi_item` FOR EACH ROW BEGIN
    DECLARE produk_kurang_terjual VARCHAR(100);
    DECLARE produk_id INT;
    DECLARE min_penjualan INT;

    
    SELECT IdProduk, SUM(Jumlah) INTO produk_id, min_penjualan
    FROM transaksi_item
    WHERE IdProduk = NEW.IdProduk
    GROUP BY IdProduk
    ORDER BY min_penjualan ASC
    LIMIT 1;

    
    SELECT NamaProduk INTO produk_kurang_terjual
    FROM produk
    WHERE IdProduk = produk_id;

    
    UPDATE laporan_penjualan
    SET ProdukKurangLaku = produk_kurang_terjual
    WHERE IdLaporan = (
        SELECT IdLaporan FROM (
            SELECT MAX(IdLaporan) AS IdLaporan FROM laporan_penjualan
        ) AS temp_table
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_produk_terlaris` AFTER INSERT ON `transaksi_item` FOR EACH ROW BEGIN
    DECLARE produk_terlaris VARCHAR(100);
    DECLARE produk_id INT;
    DECLARE max_penjualan INT;

    
    SELECT IdProduk, SUM(Jumlah) INTO produk_id, max_penjualan
    FROM transaksi_item
    WHERE IdProduk = NEW.IdProduk
    GROUP BY IdProduk
    ORDER BY max_penjualan DESC
    LIMIT 1;

    
    SELECT NamaProduk INTO produk_terlaris
    FROM produk
    WHERE IdProduk = produk_id;

    
    UPDATE laporan_penjualan
    SET ProdukTerlaris = produk_terlaris
    WHERE IdLaporan = (
        SELECT IdLaporan FROM (
            SELECT MAX(IdLaporan) AS IdLaporan FROM laporan_penjualan
        ) AS temp_table
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_stok_produk` AFTER INSERT ON `transaksi_item` FOR EACH ROW BEGIN
    
    UPDATE produk
    SET Stok = Stok - NEW.Jumlah
    WHERE IdProduk = NEW.IdProduk;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_total_bayar` AFTER INSERT ON `transaksi_item` FOR EACH ROW BEGIN
    UPDATE transaksi
    SET TotalBayar = (
        SELECT SUM(Subtotal) FROM transaksi_item WHERE IdTransaksi = NEW.IdTransaksi
    )
    WHERE IdTransaksi = NEW.IdTransaksi;
END
$$
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
