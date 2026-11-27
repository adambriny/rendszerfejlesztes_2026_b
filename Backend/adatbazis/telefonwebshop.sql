-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Nov 27. 16:34
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `telefonwebshop`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `felhasznalok`
--

CREATE TABLE `felhasznalok` (
  `felhasznalo_id` int(11) NOT NULL,
  `nev` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `jelszo_hash` varchar(255) NOT NULL,
  `szerepkor` varchar(20) NOT NULL DEFAULT 'vasarlo',
  `aktiv` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `felhasznalok`
--

INSERT INTO `felhasznalok` (`felhasznalo_id`, `nev`, `email`, `jelszo_hash`, `szerepkor`, `aktiv`) VALUES
(1, 'admin', 'admin@admin.com', '$2y$10$imWEGHZIhucVWGTwU3h/dODl5Sybwc4fENt.CROsrX/2vuypsYgmi', 'admin', 1),
(2, 'Példa Anna', 'anna@pelda.com', 'AnnaJelszo123!', 'vasarlo', 1),
(3, 'Példa Péter', 'peter@pelda.com', 'PeterJelszo123!', 'vasarlo', 1),
(5, 'Példa Béla', 'bela@pelda.com', '$2y$10$leYjgBUuyz5ZFpFgGeW0ceYLohMfjn6.iYmW/aXq4dECEnboiF.OS', 'vasarlo', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `rendelesek`
--

CREATE TABLE `rendelesek` (
  `rendeles_id` int(11) NOT NULL,
  `felhasznalo_id` int(11) NOT NULL,
  `rendeles_datuma` datetime NOT NULL,
  `statusz` varchar(30) NOT NULL DEFAULT 'leadva',
  `szallitasi_adatok` text NOT NULL,
  `szamlazasi_adatok` text DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `rendeles_tetelek`
--

CREATE TABLE `rendeles_tetelek` (
  `tetel_id` int(11) NOT NULL,
  `rendeles_id` int(11) NOT NULL,
  `valtozat_id` int(11) NOT NULL,
  `mennyiseg` int(11) NOT NULL CHECK (`mennyiseg` > 0),
  `egysegar` decimal(10,2) NOT NULL CHECK (`egysegar` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `telefonmodellek`
--

CREATE TABLE `telefonmodellek` (
  `modell_id` int(11) NOT NULL,
  `marka` varchar(50) NOT NULL,
  `modell_nev` varchar(100) NOT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `telefonmodellek`
--

INSERT INTO `telefonmodellek` (`modell_id`, `marka`, `modell_nev`, `aktiv`) VALUES
(1, 'Apple', 'iPhone 15', 1),
(2, 'Apple', 'iPhone 14', 1),
(3, 'Apple', 'iPhone 13', 1),
(4, 'Apple', 'iPhone 12', 1),
(5, 'Samsung', 'Galaxy S24', 1),
(6, 'Samsung', 'Galaxy S24 Ultra', 1),
(7, 'Samsung', 'Galaxy S23', 1),
(8, 'Samsung', 'Galaxy A54', 1),
(9, 'Samsung', 'Galaxy A34', 1),
(10, 'Xiaomi', 'Redmi Note 13', 1),
(11, 'Xiaomi', 'Redmi 13T', 1),
(12, 'Huawei', 'P60 Pro', 1),
(13, 'Google', 'Pixel 8', 1),
(14, 'OnePlus', 'OnePlus 12', 1),
(15, 'Apple', 'iPhone 15', 1),
(16, 'Apple', 'iPhone 14', 1),
(17, 'Apple', 'iPhone 13', 1),
(18, 'Apple', 'iPhone 12', 1),
(19, 'Samsung', 'Galaxy S24', 1),
(20, 'Samsung', 'Galaxy S24 Ultra', 1),
(21, 'Samsung', 'Galaxy S23', 1),
(22, 'Samsung', 'Galaxy A54', 1),
(23, 'Samsung', 'Galaxy A34', 1),
(24, 'Xiaomi', 'Redmi Note 13', 1),
(25, 'Xiaomi', 'Redmi 13T', 1),
(26, 'Huawei', 'P60 Pro', 1),
(27, 'Google', 'Pixel 8', 1),
(28, 'OnePlus', 'OnePlus 12', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `termekek`
--

CREATE TABLE `termekek` (
  `termek_id` int(11) NOT NULL,
  `nev` varchar(150) NOT NULL,
  `leiras` text NOT NULL,
  `minta` varchar(50) NOT NULL,
  `anyag` varchar(50) NOT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `termekek`
--

INSERT INTO `termekek` (`termek_id`, `nev`, `leiras`, `minta`, `anyag`, `aktiv`) VALUES
(1, 'Prémium Folyékony Szilikon Tok', 'Puha tapintású, mikroszálas belsős szilikon tok, amely tökéletesen védi a telefont a karcolásoktól és ütésektől.', 'Egyszínű', 'Szilikon', 1),
(2, 'Elegáns Márvány mintás Tok', 'Stílusos, fényes felületű márvány mintás védőtok stílusos megjelenést biztosítva.', 'Márvány', 'Fa', 1),
(3, 'Ultra Rugalmas Crystal Clear Tok', 'Teljesen átlátszó, vékony tok, amely megőrzi a telefon eredeti dizájnját.', 'Átlátszó', 'Szilikon', 1),
(4, 'Carbon Hatású Ütésálló Páncél Tok', 'Katonai szintű védelem a sarkokon megerősített ütközéssel és modern karbon mintával.', 'Karbon', 'TPU', 1),
(5, 'Prémium Bőr Hatású Védőtok', 'Prémium textúrájú bőr hatású tok elegáns megjelenéssel és biztos fogással.', 'Texturált', 'Bőr', 1),
(6, 'Prémium Folyékony Szilikon Tok', 'Puha tapintású, mikroszálas belsős szilikon tok, amely tökéletesen védi a telefont a karcolásoktól és ütésektől.', 'Egyszínű', 'Szilikon', 1),
(7, 'Elegáns Márvány mintás Tok', 'Stílusos, fényes felületű márvány mintás védőtok stílusos megjelenést biztosítva.', 'Márvány', 'Fa', 1),
(8, 'Ultra Rugalmas Crystal Clear Tok', 'Teljesen átlátszó, vékony tok, amely megőrzi a telefon eredeti dizájnját.', 'Átlátszó', 'Szilikon', 1),
(9, 'Carbon Hatású Ütésálló Páncél Tok', 'Katonai szintű védelem a sarkokon megerősített ütközéssel és modern karbon mintával.', 'Karbon', 'TPU', 1),
(10, 'Prémium Bőr Hatású Védőtok', 'Prémium textúrájú bőr hatású tok elegáns megjelenéssel és biztos fogással.', 'Texturált', 'Bőr', 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `termek_valtozat`
--

CREATE TABLE `termek_valtozat` (
  `valtozat_id` int(11) NOT NULL,
  `termek_id` int(11) NOT NULL,
  `modell_id` int(11) NOT NULL,
  `szin` varchar(30) NOT NULL,
  `ar` decimal(10,2) NOT NULL CHECK (`ar` > 0),
  `keszlet` int(11) NOT NULL CHECK (`keszlet` >= 0),
  `kep_url` varchar(255) NOT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

--
-- A tábla adatainak kiíratása `termek_valtozat`
--

INSERT INTO `termek_valtozat` (`valtozat_id`, `termek_id`, `modell_id`, `szin`, `ar`, `keszlet`, `kep_url`, `aktiv`) VALUES
(1, 1, 1, 'átlátszó', 3579.00, 7, 'placeholder.jpg', 1),
(2, 1, 2, 'kék', 5733.00, 19, 'placeholder.jpg', 1),
(3, 1, 3, 'átlátszó', 4627.00, 5, 'placeholder.jpg', 1),
(4, 1, 4, 'fekete', 7242.00, 12, 'placeholder.jpg', 1),
(5, 1, 5, 'rózsaszín', 2709.00, 7, 'placeholder.jpg', 1),
(6, 1, 6, 'rózsaszín', 7531.00, 11, 'placeholder.jpg', 1),
(7, 1, 7, 'zöld', 2452.00, 10, 'placeholder.jpg', 1),
(8, 1, 8, 'átlátszó', 6547.00, 20, 'placeholder.jpg', 1),
(9, 1, 9, 'kék', 6703.00, 7, 'placeholder.jpg', 1),
(10, 1, 10, 'átlátszó', 2097.00, 7, 'placeholder.jpg', 1),
(11, 1, 11, 'zöld', 3394.00, 14, 'placeholder.jpg', 1),
(12, 1, 12, 'átlátszó', 3178.00, 6, 'placeholder.jpg', 1),
(13, 1, 13, 'piros', 4229.00, 25, 'placeholder.jpg', 1),
(14, 1, 14, 'kék', 3212.00, 5, 'placeholder.jpg', 1),
(15, 2, 1, 'fekete', 5717.00, 18, 'placeholder.jpg', 1),
(16, 2, 2, 'rózsaszín', 6409.00, 15, 'placeholder.jpg', 1),
(17, 2, 3, 'kék', 5808.00, 11, 'placeholder.jpg', 1),
(18, 2, 4, 'zöld', 3945.00, 15, 'placeholder.jpg', 1),
(19, 2, 5, 'kék', 7210.00, 14, 'placeholder.jpg', 1),
(20, 2, 6, 'fekete', 2663.00, 20, 'placeholder.jpg', 1),
(21, 2, 7, 'fekete', 7451.00, 16, 'placeholder.jpg', 1),
(22, 2, 8, 'rózsaszín', 4124.00, 16, 'placeholder.jpg', 1),
(23, 2, 9, 'zöld', 5057.00, 18, 'placeholder.jpg', 1),
(24, 2, 10, 'fekete', 6675.00, 13, 'placeholder.jpg', 1),
(25, 2, 11, 'rózsaszín', 4214.00, 17, 'placeholder.jpg', 1),
(26, 2, 12, 'kék', 4600.00, 24, 'placeholder.jpg', 1),
(27, 2, 13, 'fekete', 3452.00, 14, 'placeholder.jpg', 1),
(28, 2, 14, 'kék', 3955.00, 23, 'placeholder.jpg', 1),
(29, 3, 1, 'átlátszó', 2467.00, 13, 'placeholder.jpg', 1),
(30, 3, 2, 'átlátszó', 4443.00, 6, 'placeholder.jpg', 1),
(31, 3, 3, 'zöld', 3544.00, 18, 'placeholder.jpg', 1),
(32, 3, 4, 'átlátszó', 6794.00, 8, 'placeholder.jpg', 1),
(33, 3, 5, 'rózsaszín', 4239.00, 23, 'placeholder.jpg', 1),
(34, 3, 6, 'kék', 3893.00, 15, 'placeholder.jpg', 1),
(35, 3, 7, 'fehér', 5602.00, 10, 'placeholder.jpg', 1),
(36, 3, 8, 'rózsaszín', 4052.00, 20, 'placeholder.jpg', 1),
(37, 3, 9, 'zöld', 4178.00, 14, 'placeholder.jpg', 1),
(38, 3, 10, 'fekete', 4702.00, 18, 'placeholder.jpg', 1),
(39, 3, 11, 'piros', 2631.00, 20, 'placeholder.jpg', 1),
(40, 3, 12, 'zöld', 3949.00, 8, 'placeholder.jpg', 1),
(41, 3, 13, 'kék', 4487.00, 17, 'placeholder.jpg', 1),
(42, 3, 14, 'piros', 2707.00, 15, 'placeholder.jpg', 1),
(43, 4, 1, 'fehér', 4449.00, 10, 'placeholder.jpg', 1),
(44, 4, 2, 'fehér', 7292.00, 16, 'placeholder.jpg', 1),
(45, 4, 3, 'fehér', 3030.00, 25, '../../kepek/Apple_iPhone_13_feher.jpg', 1),
(46, 4, 4, 'rózsaszín', 7154.00, 5, '../../kepek/Apple_iPhone_12_rozsaszin.jpg', 1),
(47, 4, 5, 'rózsaszín', 4425.00, 7, 'placeholder.jpg', 1),
(48, 4, 6, 'piros', 4422.00, 8, 'placeholder.jpg', 1),
(49, 4, 7, 'zöld', 7510.00, 15, 'placeholder.jpg', 1),
(50, 4, 8, 'piros', 5101.00, 16, 'placeholder.jpg', 1),
(51, 4, 9, 'kék', 7956.00, 12, '../../kepek/Samsung_Galaxy_A34_kek.jpg', 1),
(52, 4, 10, 'fekete', 6802.00, 16, 'placeholder.jpg', 1),
(53, 4, 11, 'rózsaszín', 7368.00, 14, '../../kepek/Xiaomi_Redmi_13T_rozsaszin.jpg', 1),
(54, 4, 12, 'piros', 3973.00, 10, '../../kepek/Huawei_P60_Pro_piros.jpg', 1),
(55, 4, 13, 'fekete', 4229.00, 16, '../../kepek/Google_Pixel_8_fekete.jpg', 1),
(56, 4, 14, 'kék', 2781.00, 8, '../../kepek/OnePlus_OnePlus_12_kek.jpg', 1),
(57, 5, 1, 'fehér', 7221.00, 17, 'placeholder.jpg', 1),
(58, 5, 2, 'fekete', 5911.00, 17, 'placeholder.jpg', 1),
(59, 5, 3, 'kék', 5136.00, 9, 'placeholder.jpg', 1),
(60, 5, 4, 'átlátszó', 5177.00, 14, 'placeholder.jpg', 1),
(61, 5, 5, 'átlátszó', 6541.00, 20, 'placeholder.jpg', 1),
(62, 5, 6, 'átlátszó', 4209.00, 23, 'placeholder.jpg', 1),
(63, 5, 7, 'piros', 4882.00, 7, 'placeholder.jpg', 1),
(64, 5, 8, 'rózsaszín', 6916.00, 24, 'placeholder.jpg', 1),
(65, 5, 9, 'kék', 3658.00, 18, 'placeholder.jpg', 1),
(66, 5, 10, 'fekete', 7479.00, 7, 'placeholder.jpg', 1),
(67, 5, 11, 'zöld', 7637.00, 19, 'placeholder.jpg', 1),
(68, 5, 12, 'fehér', 6781.00, 17, 'placeholder.jpg', 1),
(69, 5, 13, 'rózsaszín', 2126.00, 6, 'placeholder.jpg', 1),
(70, 5, 14, 'fekete', 4847.00, 18, 'placeholder.jpg', 1),
(71, 1, 1, 'rózsaszín', 7864.00, 15, 'placeholder.jpg', 1),
(72, 1, 2, 'piros', 2827.00, 25, 'placeholder.jpg', 1),
(73, 1, 3, 'piros', 5475.00, 7, 'placeholder.jpg', 1),
(74, 1, 4, 'rózsaszín', 4732.00, 6, 'placeholder.jpg', 1),
(75, 1, 5, 'átlátszó', 4961.00, 15, 'placeholder.jpg', 1),
(76, 1, 6, 'fekete', 2882.00, 23, 'placeholder.jpg', 1),
(77, 1, 7, 'zöld', 6648.00, 13, 'placeholder.jpg', 1),
(78, 1, 8, 'piros', 5457.00, 9, 'placeholder.jpg', 1),
(79, 1, 9, 'fekete', 4783.00, 22, 'placeholder.jpg', 1),
(80, 1, 10, 'fehér', 3469.00, 5, 'placeholder.jpg', 1),
(81, 1, 11, 'fehér', 7951.00, 10, 'placeholder.jpg', 1),
(82, 1, 12, 'rózsaszín', 7803.00, 13, 'placeholder.jpg', 1),
(83, 1, 13, 'piros', 6060.00, 23, 'placeholder.jpg', 1),
(84, 1, 14, 'piros', 6855.00, 11, 'placeholder.jpg', 1),
(85, 1, 15, 'fekete', 4609.00, 8, 'placeholder.jpg', 1),
(86, 1, 16, 'piros', 2908.00, 12, 'placeholder.jpg', 1),
(87, 1, 17, 'rózsaszín', 4059.00, 17, 'placeholder.jpg', 1),
(88, 1, 18, 'fekete', 7033.00, 16, 'placeholder.jpg', 1),
(89, 1, 19, 'piros', 4339.00, 9, 'placeholder.jpg', 1),
(90, 1, 20, 'fekete', 3984.00, 7, 'placeholder.jpg', 1),
(91, 1, 21, 'kék', 6495.00, 19, 'placeholder.jpg', 1),
(92, 1, 22, 'fekete', 7715.00, 23, 'placeholder.jpg', 1),
(93, 1, 23, 'piros', 7720.00, 20, 'placeholder.jpg', 1),
(94, 1, 24, 'fehér', 5092.00, 6, 'placeholder.jpg', 1),
(95, 1, 25, 'zöld', 7957.00, 14, 'placeholder.jpg', 1),
(96, 1, 26, 'rózsaszín', 6675.00, 24, 'placeholder.jpg', 1),
(97, 1, 27, 'zöld', 2851.00, 22, 'placeholder.jpg', 1),
(98, 1, 28, 'átlátszó', 4091.00, 13, 'placeholder.jpg', 1),
(99, 2, 1, 'piros', 3467.00, 21, 'placeholder.jpg', 1),
(100, 2, 2, 'zöld', 3723.00, 8, 'placeholder.jpg', 1),
(101, 2, 3, 'fekete', 7452.00, 8, 'placeholder.jpg', 1),
(102, 2, 4, 'rózsaszín', 2734.00, 10, 'placeholder.jpg', 1),
(103, 2, 5, 'fekete', 4998.00, 6, 'placeholder.jpg', 1),
(104, 2, 6, 'fekete', 6054.00, 20, 'placeholder.jpg', 1),
(105, 2, 7, 'piros', 3613.00, 15, 'placeholder.jpg', 1),
(106, 2, 8, 'fehér', 4470.00, 16, 'placeholder.jpg', 1),
(107, 2, 9, 'kék', 7944.00, 10, 'placeholder.jpg', 1),
(108, 2, 10, 'kék', 5207.00, 5, 'placeholder.jpg', 1),
(109, 2, 11, 'fehér', 6459.00, 15, 'placeholder.jpg', 1),
(110, 2, 12, 'zöld', 7022.00, 13, 'placeholder.jpg', 1),
(111, 2, 13, 'fehér', 4381.00, 21, 'placeholder.jpg', 1),
(112, 2, 14, 'átlátszó', 5760.00, 16, '../../kepek/OnePlus_OnePlus_12_marvany_atlatszo.jpg', 1),
(113, 2, 15, 'kék', 6272.00, 12, 'placeholder.jpg', 1),
(114, 2, 16, 'fehér', 2970.00, 24, 'placeholder.jpg', 1),
(115, 2, 17, 'piros', 7896.00, 23, 'placeholder.jpg', 1),
(116, 2, 18, 'zöld', 5238.00, 17, 'placeholder.jpg', 1),
(117, 2, 19, 'fekete', 4665.00, 11, 'placeholder.jpg', 1),
(118, 2, 20, 'rózsaszín', 6946.00, 22, 'placeholder.jpg', 1),
(119, 2, 21, 'kék', 2806.00, 14, 'placeholder.jpg', 1),
(120, 2, 22, 'zöld', 6105.00, 17, 'placeholder.jpg', 1),
(121, 2, 23, 'kék', 3882.00, 5, 'placeholder.jpg', 1),
(122, 2, 24, 'kék', 4398.00, 9, 'placeholder.jpg', 1),
(123, 2, 25, 'zöld', 3897.00, 19, 'placeholder.jpg', 1),
(124, 2, 26, 'kék', 6005.00, 23, 'placeholder.jpg', 1),
(125, 2, 27, 'átlátszó', 2833.00, 16, '../../kepek/Google_Pixel_8_marvany_atlatszo.jpg', 1),
(126, 2, 28, 'kék', 2408.00, 14, 'placeholder.jpg', 1),
(127, 3, 1, 'fekete', 4065.00, 16, 'placeholder.jpg', 1),
(128, 3, 2, 'fehér', 5441.00, 11, 'placeholder.jpg', 1),
(129, 3, 3, 'fehér', 2034.00, 10, 'placeholder.jpg', 1),
(130, 3, 4, 'átlátszó', 2702.00, 17, 'placeholder.jpg', 1),
(131, 3, 5, 'fehér', 4013.00, 23, 'placeholder.jpg', 1),
(132, 3, 6, 'kék', 5604.00, 10, 'placeholder.jpg', 1),
(133, 3, 7, 'átlátszó', 4114.00, 5, 'placeholder.jpg', 1),
(134, 3, 8, 'rózsaszín', 4160.00, 13, 'placeholder.jpg', 1),
(135, 3, 9, 'zöld', 4935.00, 21, 'placeholder.jpg', 1),
(136, 3, 10, 'zöld', 4529.00, 5, 'placeholder.jpg', 1),
(137, 3, 11, 'piros', 5376.00, 17, 'placeholder.jpg', 1),
(138, 3, 12, 'átlátszó', 7273.00, 21, 'placeholder.jpg', 1),
(139, 3, 13, 'átlátszó', 5289.00, 11, 'placeholder.jpg', 1),
(140, 3, 14, 'piros', 3121.00, 10, 'placeholder.jpg', 1),
(141, 3, 15, 'fekete', 4121.00, 7, 'placeholder.jpg', 1),
(142, 3, 16, 'kék', 7451.00, 5, 'placeholder.jpg', 1),
(143, 3, 17, 'fekete', 4344.00, 24, 'placeholder.jpg', 1),
(144, 3, 18, 'átlátszó', 5661.00, 8, 'placeholder.jpg', 1),
(145, 3, 19, 'fekete', 4963.00, 11, 'placeholder.jpg', 1),
(146, 3, 20, 'zöld', 2220.00, 9, 'placeholder.jpg', 1),
(147, 3, 21, 'zöld', 5516.00, 15, 'placeholder.jpg', 1),
(148, 3, 22, 'rózsaszín', 7681.00, 25, 'placeholder.jpg', 1),
(149, 3, 23, 'átlátszó', 7380.00, 5, 'placeholder.jpg', 1),
(150, 3, 24, 'rózsaszín', 5559.00, 12, 'placeholder.jpg', 1),
(151, 3, 25, 'kék', 3192.00, 22, 'placeholder.jpg', 1),
(152, 3, 26, 'fekete', 7856.00, 12, 'placeholder.jpg', 1),
(153, 3, 27, 'kék', 6717.00, 20, 'placeholder.jpg', 1),
(154, 3, 28, 'fehér', 6408.00, 7, 'placeholder.jpg', 1),
(155, 4, 1, 'rózsaszín', 4367.00, 9, 'placeholder.jpg', 1),
(156, 4, 2, 'átlátszó', 3761.00, 13, 'placeholder.jpg', 1),
(157, 4, 3, 'rózsaszín', 6998.00, 5, 'placeholder.jpg', 1),
(158, 4, 4, 'kék', 4221.00, 18, '../../kepek/Apple_iPhone_12_kek.jpg', 1),
(159, 4, 5, 'zöld', 4972.00, 7, 'placeholder.jpg', 1),
(160, 4, 6, 'átlátszó', 4345.00, 22, 'placeholder.jpg', 1),
(161, 4, 7, 'kék', 4990.00, 20, 'placeholder.jpg', 1),
(162, 4, 8, 'kék', 5832.00, 22, 'placeholder.jpg', 1),
(163, 4, 9, 'fehér', 5556.00, 12, '../../kepek/Samsung_Galaxy_A34_feher.jpg', 1),
(164, 4, 10, 'átlátszó', 5627.00, 5, '../../kepek/Xiaomi_Redmi_Note_13_atlatszo.jpg', 1),
(165, 4, 11, 'fekete', 5201.00, 10, '../../kepek/Xiaomi_Redmi_13T_fekete.jpg', 1),
(166, 4, 12, 'zöld', 5316.00, 17, '../../kepek/Huawei_P60_Pro_zold.jpg', 1),
(167, 4, 13, 'átlátszó', 4023.00, 7, '../../kepek/Google_Pixel_8_atlatszo.jpg', 1),
(168, 4, 14, 'rózsaszín', 3555.00, 20, '../../kepek/OnePlus_OnePlus_12_rozsaszin.jpg', 1),
(169, 4, 15, 'kék', 7320.00, 6, 'placeholder.jpg', 1),
(170, 4, 16, 'fehér', 6600.00, 11, 'placeholder.jpg', 1),
(171, 4, 17, 'fekete', 5728.00, 13, 'placeholder.jpg', 1),
(172, 4, 18, 'zöld', 5082.00, 11, '../../kepek/Apple_iPhone_12_zold.jpg', 1),
(173, 4, 19, 'fekete', 3727.00, 9, 'placeholder.jpg', 1),
(174, 4, 20, 'kék', 4165.00, 15, 'placeholder.jpg', 1),
(175, 4, 21, 'fehér', 4750.00, 18, 'placeholder.jpg', 1),
(176, 4, 22, 'átlátszó', 3183.00, 9, '../../kepek/Samsung_Galaxy_A54_atlatszo.jpg', 1),
(177, 4, 23, 'rózsaszín', 6675.00, 10, '../../kepek/Samsung_Galaxy_A34_rozsaszin.jpg', 1),
(178, 4, 24, 'rózsaszín', 4890.00, 18, 'placeholder.jpg', 1),
(179, 4, 25, 'zöld', 6672.00, 12, '../../kepek/Xiaomi_Redmi_13T_zold.jpg', 1),
(180, 4, 26, 'rózsaszín', 3862.00, 22, '../../kepek/Huawei_P60_Pro_rozsaszin.jpg', 1),
(181, 4, 27, 'fekete', 4731.00, 15, '../../kepek/Google_Pixel_8_fekete.jpg', 1),
(182, 4, 28, 'átlátszó', 6882.00, 16, '../../kepek/OnePlus_OnePlus_12_atlatszo.jpg', 1),
(183, 5, 1, 'rózsaszín', 2198.00, 17, 'placeholder.jpg', 1),
(184, 5, 2, 'piros', 7954.00, 13, 'placeholder.jpg', 1),
(185, 5, 3, 'kék', 3980.00, 23, 'placeholder.jpg', 1),
(186, 5, 4, 'fehér', 6073.00, 5, 'placeholder.jpg', 1),
(187, 5, 5, 'kék', 3273.00, 6, 'placeholder.jpg', 1),
(188, 5, 6, 'zöld', 2361.00, 15, 'placeholder.jpg', 1),
(189, 5, 7, 'kék', 6376.00, 25, 'placeholder.jpg', 1),
(190, 5, 8, 'rózsaszín', 5515.00, 23, 'placeholder.jpg', 1),
(191, 5, 9, 'zöld', 3861.00, 7, 'placeholder.jpg', 1),
(192, 5, 10, 'fekete', 7350.00, 25, 'placeholder.jpg', 1),
(193, 5, 11, 'piros', 7009.00, 16, 'placeholder.jpg', 1),
(194, 5, 12, 'kék', 7457.00, 5, 'placeholder.jpg', 1),
(195, 5, 13, 'átlátszó', 3756.00, 11, 'placeholder.jpg', 1),
(196, 5, 14, 'rózsaszín', 5767.00, 19, 'placeholder.jpg', 1),
(197, 5, 15, 'átlátszó', 7653.00, 16, 'placeholder.jpg', 1),
(198, 5, 16, 'zöld', 4615.00, 12, 'placeholder.jpg', 1),
(199, 5, 17, 'átlátszó', 6502.00, 24, 'placeholder.jpg', 1),
(200, 5, 18, 'rózsaszín', 3376.00, 5, 'placeholder.jpg', 1),
(201, 5, 19, 'átlátszó', 7378.00, 17, 'placeholder.jpg', 1),
(202, 5, 20, 'átlátszó', 6514.00, 19, 'placeholder.jpg', 1),
(203, 5, 21, 'fekete', 5730.00, 5, 'placeholder.jpg', 1),
(204, 5, 22, 'kék', 4865.00, 24, 'placeholder.jpg', 1),
(205, 5, 23, 'fehér', 5329.00, 13, 'placeholder.jpg', 1),
(206, 5, 24, 'átlátszó', 6152.00, 19, 'placeholder.jpg', 1),
(207, 5, 25, 'átlátszó', 2108.00, 21, 'placeholder.jpg', 1),
(208, 5, 26, 'kék', 6208.00, 6, 'placeholder.jpg', 1),
(209, 5, 27, 'fehér', 6183.00, 7, 'placeholder.jpg', 1),
(210, 5, 28, 'fekete', 7000.00, 5, 'placeholder.jpg', 1),
(211, 6, 1, 'kék', 3534.00, 24, 'placeholder.jpg', 1),
(212, 6, 2, 'kék', 3383.00, 23, 'placeholder.jpg', 1),
(213, 6, 3, 'rózsaszín', 4091.00, 17, 'placeholder.jpg', 1),
(214, 6, 4, 'piros', 3774.00, 25, 'placeholder.jpg', 1),
(215, 6, 5, 'piros', 2738.00, 12, 'placeholder.jpg', 1),
(216, 6, 6, 'fekete', 2844.00, 19, 'placeholder.jpg', 1),
(217, 6, 7, 'zöld', 3335.00, 7, 'placeholder.jpg', 1),
(218, 6, 8, 'fekete', 5155.00, 5, 'placeholder.jpg', 1),
(219, 6, 9, 'fehér', 4792.00, 15, 'placeholder.jpg', 1),
(220, 6, 10, 'fehér', 5172.00, 17, 'placeholder.jpg', 1),
(221, 6, 11, 'rózsaszín', 3387.00, 7, 'placeholder.jpg', 1),
(222, 6, 12, 'fekete', 2995.00, 7, 'placeholder.jpg', 1),
(223, 6, 13, 'rózsaszín', 2563.00, 22, 'placeholder.jpg', 1),
(224, 6, 14, 'fehér', 3788.00, 14, 'placeholder.jpg', 1),
(225, 6, 15, 'rózsaszín', 6510.00, 25, 'placeholder.jpg', 1),
(226, 6, 16, 'rózsaszín', 6590.00, 10, 'placeholder.jpg', 1),
(227, 6, 17, 'piros', 5037.00, 9, 'placeholder.jpg', 1),
(228, 6, 18, 'zöld', 2538.00, 13, 'placeholder.jpg', 1),
(229, 6, 19, 'zöld', 2983.00, 5, 'placeholder.jpg', 1),
(230, 6, 20, 'fehér', 5171.00, 5, 'placeholder.jpg', 1),
(231, 6, 21, 'átlátszó', 4188.00, 13, 'placeholder.jpg', 1),
(232, 6, 22, 'piros', 2531.00, 10, 'placeholder.jpg', 1),
(233, 6, 23, 'fekete', 6995.00, 22, 'placeholder.jpg', 1),
(234, 6, 24, 'rózsaszín', 3849.00, 20, 'placeholder.jpg', 1),
(235, 6, 25, 'rózsaszín', 7159.00, 13, 'placeholder.jpg', 1),
(236, 6, 26, 'fekete', 4428.00, 19, 'placeholder.jpg', 1),
(237, 6, 27, 'fekete', 5198.00, 12, 'placeholder.jpg', 1),
(238, 6, 28, 'fehér', 7221.00, 8, 'placeholder.jpg', 1),
(239, 7, 1, 'rózsaszín', 5518.00, 22, 'placeholder.jpg', 1),
(240, 7, 2, 'rózsaszín', 6328.00, 23, 'placeholder.jpg', 1),
(241, 7, 3, 'fehér', 4895.00, 12, 'placeholder.jpg', 1),
(242, 7, 4, 'fekete', 7496.00, 13, 'placeholder.jpg', 1),
(243, 7, 5, 'piros', 3037.00, 24, 'placeholder.jpg', 1),
(244, 7, 6, 'fekete', 4619.00, 7, 'placeholder.jpg', 1),
(245, 7, 7, 'fehér', 4233.00, 18, 'placeholder.jpg', 1),
(246, 7, 8, 'rózsaszín', 5562.00, 14, 'placeholder.jpg', 1),
(247, 7, 9, 'átlátszó', 6111.00, 18, 'placeholder.jpg', 1),
(248, 7, 10, 'zöld', 2892.00, 25, 'placeholder.jpg', 1),
(249, 7, 11, 'kék', 5486.00, 17, 'placeholder.jpg', 1),
(250, 7, 12, 'rózsaszín', 3866.00, 23, 'placeholder.jpg', 1),
(251, 7, 13, 'fehér', 2018.00, 7, 'placeholder.jpg', 1),
(252, 7, 14, 'fehér', 6829.00, 14, 'placeholder.jpg', 1),
(253, 7, 15, 'zöld', 6901.00, 23, 'placeholder.jpg', 1),
(254, 7, 16, 'fekete', 2960.00, 15, 'placeholder.jpg', 1),
(255, 7, 17, 'fehér', 3787.00, 7, 'placeholder.jpg', 1),
(256, 7, 18, 'átlátszó', 7190.00, 15, 'placeholder.jpg', 1),
(257, 7, 19, 'zöld', 4749.00, 19, 'placeholder.jpg', 1),
(258, 7, 20, 'átlátszó', 7787.00, 12, 'placeholder.jpg', 1),
(259, 7, 21, 'zöld', 3500.00, 23, 'placeholder.jpg', 1),
(260, 7, 22, 'átlátszó', 4369.00, 6, 'placeholder.jpg', 1),
(261, 7, 23, 'fekete', 7641.00, 18, 'placeholder.jpg', 1),
(262, 7, 24, 'kék', 2146.00, 18, 'placeholder.jpg', 1),
(263, 7, 25, 'zöld', 7833.00, 21, 'placeholder.jpg', 1),
(264, 7, 26, 'fekete', 3875.00, 20, '../../kepek/Huawei_P60_Pro_marvany_fekete.jpg', 1),
(265, 7, 27, 'kék', 5378.00, 6, 'placeholder.jpg', 1),
(266, 7, 28, 'kék', 2506.00, 22, 'placeholder.jpg', 1),
(267, 8, 1, 'piros', 3813.00, 25, 'placeholder.jpg', 1),
(268, 8, 2, 'átlátszó', 3510.00, 5, 'placeholder.jpg', 1),
(269, 8, 3, 'piros', 3681.00, 24, 'placeholder.jpg', 1),
(270, 8, 4, 'rózsaszín', 7437.00, 20, 'placeholder.jpg', 1),
(271, 8, 5, 'rózsaszín', 4744.00, 25, 'placeholder.jpg', 1),
(272, 8, 6, 'átlátszó', 7299.00, 10, 'placeholder.jpg', 1),
(273, 8, 7, 'átlátszó', 2927.00, 12, 'placeholder.jpg', 1),
(274, 8, 8, 'kék', 3654.00, 14, 'placeholder.jpg', 1),
(275, 8, 9, 'fekete', 6668.00, 25, 'placeholder.jpg', 1),
(276, 8, 10, 'fekete', 7663.00, 5, 'placeholder.jpg', 1),
(277, 8, 11, 'fehér', 7377.00, 10, 'placeholder.jpg', 1),
(278, 8, 12, 'átlátszó', 4353.00, 20, 'placeholder.jpg', 1),
(279, 8, 13, 'fekete', 4949.00, 6, 'placeholder.jpg', 1),
(280, 8, 14, 'átlátszó', 5717.00, 17, 'placeholder.jpg', 1),
(281, 8, 15, 'rózsaszín', 7412.00, 11, 'placeholder.jpg', 1),
(282, 8, 16, 'rózsaszín', 7391.00, 23, 'placeholder.jpg', 1),
(283, 8, 17, 'piros', 4930.00, 18, 'placeholder.jpg', 1),
(284, 8, 18, 'fehér', 3209.00, 8, 'placeholder.jpg', 1),
(285, 8, 19, 'rózsaszín', 6988.00, 15, 'placeholder.jpg', 1),
(286, 8, 20, 'zöld', 5788.00, 14, 'placeholder.jpg', 1),
(287, 8, 21, 'piros', 4243.00, 23, 'placeholder.jpg', 1),
(288, 8, 22, 'zöld', 5629.00, 5, 'placeholder.jpg', 1),
(289, 8, 23, 'fehér', 7809.00, 11, 'placeholder.jpg', 1),
(290, 8, 24, 'kék', 5349.00, 21, 'placeholder.jpg', 1),
(291, 8, 25, 'átlátszó', 5949.00, 6, 'placeholder.jpg', 1),
(292, 8, 26, 'fekete', 6473.00, 18, 'placeholder.jpg', 1),
(293, 8, 27, 'zöld', 3369.00, 11, 'placeholder.jpg', 1),
(294, 8, 28, 'kék', 7453.00, 10, 'placeholder.jpg', 1),
(295, 9, 1, 'fehér', 6297.00, 25, 'placeholder.jpg', 1),
(296, 9, 2, 'átlátszó', 2820.00, 14, 'placeholder.jpg', 1),
(297, 9, 3, 'kék', 3769.00, 10, 'placeholder.jpg', 1),
(298, 9, 4, 'rózsaszín', 4112.00, 23, '../../kepek/Apple_iPhone_12_rozsaszin.jpg', 1),
(299, 9, 5, 'rózsaszín', 6321.00, 22, 'placeholder.jpg', 1),
(300, 9, 6, 'kék', 4217.00, 10, 'placeholder.jpg', 1),
(301, 9, 7, 'fekete', 3307.00, 6, 'placeholder.jpg', 1),
(302, 9, 8, 'átlátszó', 7429.00, 17, 'placeholder.jpg', 1),
(303, 9, 9, 'kék', 5817.00, 14, '../../kepek/Samsung_Galaxy_A34_kek.jpg', 1),
(304, 9, 10, 'fehér', 7723.00, 18, 'placeholder.jpg', 1),
(305, 9, 11, 'kék', 2774.00, 7, '../../kepek/Xiaomi_Redmi_13T_kek.jpg', 1),
(306, 9, 12, 'rózsaszín', 5378.00, 15, '../../kepek/Huawei_P60_Pro_rozsaszin.jpg', 1),
(307, 9, 13, 'kék', 5624.00, 24, '../../kepek/Google_Pixel_8_kek.jpg', 1),
(308, 9, 14, 'átlátszó', 4099.00, 12, '../../kepek/OnePlus_OnePlus_12_atlatszo.jpg', 1),
(309, 9, 15, 'rózsaszín', 7332.00, 9, 'placeholder.jpg', 1),
(310, 9, 16, 'fehér', 7557.00, 17, 'placeholder.jpg', 1),
(311, 9, 17, 'rózsaszín', 5874.00, 23, 'placeholder.jpg', 1),
(312, 9, 18, 'átlátszó', 7555.00, 19, '../../kepek/Apple_iPhone_12_atlatszo.jpg', 1),
(313, 9, 19, 'átlátszó', 5467.00, 12, 'placeholder.jpg', 1),
(314, 9, 20, 'fekete', 4135.00, 17, 'placeholder.jpg', 1),
(315, 9, 21, 'piros', 3718.00, 25, 'placeholder.jpg', 1),
(316, 9, 22, 'piros', 6165.00, 17, 'placeholder.jpg', 1),
(317, 9, 23, 'átlátszó', 6193.00, 16, '../../kepek/Samsung_Galaxy_A34_atlatszo.jpg', 1),
(318, 9, 24, 'piros', 7217.00, 8, 'placeholder.jpg', 1),
(319, 9, 25, 'fehér', 6928.00, 14, '../../kepek/Xiaomi_Redmi_13T_feher.jpg', 1),
(320, 9, 26, 'kék', 6319.00, 23, '../../kepek/Huawei_P60_Pro_kek.jpg', 1),
(321, 9, 27, 'átlátszó', 5808.00, 24, '../../kepek/Google_Pixel_8_atlatszo.jpg', 1),
(322, 9, 28, 'fehér', 6342.00, 24, '../../kepek/OnePlus_OnePlus_12_feher.jpg', 1),
(323, 10, 1, 'zöld', 4949.00, 20, 'placeholder.jpg', 1),
(324, 10, 2, 'piros', 6417.00, 7, 'placeholder.jpg', 1),
(325, 10, 3, 'átlátszó', 7758.00, 10, 'placeholder.jpg', 1),
(326, 10, 4, 'fehér', 5153.00, 1, '../../kepek/Apple_iPhone_12_bor_feher.jpg', 1),
(327, 10, 5, 'piros', 6691.00, 7, 'placeholder.jpg', 1),
(328, 10, 6, 'fehér', 2522.00, 16, 'placeholder.jpg', 1),
(329, 10, 7, 'átlátszó', 6663.00, 18, 'placeholder.jpg', 1),
(330, 10, 8, 'kék', 2150.00, 12, 'placeholder.jpg', 1),
(331, 10, 9, 'fekete', 4231.00, 13, '../../kepek/Samsung_Galaxy_A34_bor_fekete.jpg', 1),
(332, 10, 10, 'rózsaszín', 6536.00, 5, 'placeholder.jpg', 1),
(333, 10, 11, 'fekete', 5173.00, 24, '../../kepek/Xiaomi_Redmi_13T_bor_fekete.jpg', 1),
(334, 10, 12, 'fehér', 2108.00, 17, '../../kepek/Huawei_P60_Pro_bor_feher.jpg', 1),
(335, 10, 13, 'zöld', 3501.00, 9, 'placeholder.jpg', 1),
(336, 10, 14, 'zöld', 2555.00, 20, 'placeholder.jpg', 1),
(337, 10, 15, 'fehér', 2538.00, 25, 'placeholder.jpg', 1),
(338, 10, 16, 'zöld', 7434.00, 5, 'placeholder.jpg', 1),
(339, 10, 17, 'átlátszó', 2305.00, 11, 'placeholder.jpg', 1),
(340, 10, 18, 'zöld', 7853.00, 6, 'placeholder.jpg', 1),
(341, 10, 19, 'piros', 5601.00, 17, 'placeholder.jpg', 1),
(342, 10, 20, 'fehér', 4160.00, 10, 'placeholder.jpg', 1),
(343, 10, 21, 'rózsaszín', 7131.00, 10, 'placeholder.jpg', 1),
(344, 10, 22, 'zöld', 3341.00, 14, 'placeholder.jpg', 1),
(345, 10, 23, 'fekete', 5161.00, 25, 'placeholder.jpg', 1),
(346, 10, 24, 'kék', 5014.00, 25, 'placeholder.jpg', 1),
(347, 10, 25, 'fekete', 6326.00, 25, 'placeholder.jpg', 1),
(348, 10, 26, 'rózsaszín', 5405.00, 25, 'placeholder.jpg', 1),
(349, 10, 27, 'rózsaszín', 6221.00, 6, '../../kepek/Google_Pixel_8_bor_rozsaszin.jpg', 1),
(350, 10, 28, 'fehér', 7120.00, 20, '../../kepek/OnePlus_OnePlus_12_bor_feher.jpg', 1);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `felhasznalok`
--
ALTER TABLE `felhasznalok`
  ADD PRIMARY KEY (`felhasznalo_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- A tábla indexei `rendelesek`
--
ALTER TABLE `rendelesek`
  ADD PRIMARY KEY (`rendeles_id`),
  ADD KEY `felhasznalo_id` (`felhasznalo_id`);

--
-- A tábla indexei `rendeles_tetelek`
--
ALTER TABLE `rendeles_tetelek`
  ADD PRIMARY KEY (`tetel_id`),
  ADD KEY `rendeles_id` (`rendeles_id`),
  ADD KEY `valtozat_id` (`valtozat_id`);

--
-- A tábla indexei `telefonmodellek`
--
ALTER TABLE `telefonmodellek`
  ADD PRIMARY KEY (`modell_id`);

--
-- A tábla indexei `termekek`
--
ALTER TABLE `termekek`
  ADD PRIMARY KEY (`termek_id`);

--
-- A tábla indexei `termek_valtozat`
--
ALTER TABLE `termek_valtozat`
  ADD PRIMARY KEY (`valtozat_id`),
  ADD KEY `termek_id` (`termek_id`),
  ADD KEY `modell_id` (`modell_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `felhasznalok`
--
ALTER TABLE `felhasznalok`
  MODIFY `felhasznalo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT a táblához `rendelesek`
--
ALTER TABLE `rendelesek`
  MODIFY `rendeles_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `rendeles_tetelek`
--
ALTER TABLE `rendeles_tetelek`
  MODIFY `tetel_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `telefonmodellek`
--
ALTER TABLE `telefonmodellek`
  MODIFY `modell_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT a táblához `termekek`
--
ALTER TABLE `termekek`
  MODIFY `termek_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT a táblához `termek_valtozat`
--
ALTER TABLE `termek_valtozat`
  MODIFY `valtozat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=351;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `rendelesek`
--
ALTER TABLE `rendelesek`
  ADD CONSTRAINT `rendelesek_ibfk_1` FOREIGN KEY (`felhasznalo_id`) REFERENCES `felhasznalok` (`felhasznalo_id`);

--
-- Megkötések a táblához `rendeles_tetelek`
--
ALTER TABLE `rendeles_tetelek`
  ADD CONSTRAINT `rendeles_tetelek_ibfk_1` FOREIGN KEY (`rendeles_id`) REFERENCES `rendelesek` (`rendeles_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rendeles_tetelek_ibfk_2` FOREIGN KEY (`valtozat_id`) REFERENCES `termek_valtozat` (`valtozat_id`);

--
-- Megkötések a táblához `termek_valtozat`
--
ALTER TABLE `termek_valtozat`
  ADD CONSTRAINT `termek_valtozat_ibfk_1` FOREIGN KEY (`termek_id`) REFERENCES `termekek` (`termek_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `termek_valtozat_ibfk_2` FOREIGN KEY (`modell_id`) REFERENCES `telefonmodellek` (`modell_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
