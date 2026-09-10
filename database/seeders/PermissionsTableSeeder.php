<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dashboards 1 - 49
        self::seedPermission(1, 2, 1);
        self::seedPermission(2, 2, 2);
        self::seedPermission(3, 2, 3);
        self::seedPermission(4, 2, 4);
        self::seedPermission(5, 2, 5);
        self::seedPermission(6, 2, 6);
        self::seedPermission(7, 2, 7);
        self::seedPermission(8, 2, 8);
        self::seedPermission(9, 2, 9);
        self::seedPermission(10, 2, 10);
        self::seedPermission(11, 2, 11);
        self::seedPermission(12, 2, 12);
        self::seedPermission(13, 2, 13);
        self::seedPermission(14, 2, 14);
        self::seedPermission(15, 2, 15);
        self::seedPermission(16, 2, 16);
        self::seedPermission(17, 2, 17);


        // Roles 50 - 99
        self::seedPermission(50, 2, 50);
        self::seedPermission(51, 2, 51);
        self::seedPermission(52, 2, 52);
        self::seedPermission(53, 2, 53);
        self::seedPermission(54, 2, 54);

        // Users 100 - 149
        self::seedPermission(100, 2, 100);
        self::seedPermission(101, 2, 101);
        self::seedPermission(102, 2, 102);
        self::seedPermission(103, 2, 103);
        self::seedPermission(104, 2, 104);
        self::seedPermission(105, 2, 105);
        self::seedPermission(106, 2, 106);
        self::seedPermission(107, 2, 107);
        self::seedPermission(108, 2, 108);
        self::seedPermission(109, 2, 109);
        self::seedPermission(110, 2, 110);
        self::seedPermission(111, 2, 111);
        self::seedPermission(112, 2, 112);
        self::seedPermission(113, 2, 113);
        self::seedPermission(114, 2, 114);
        self::seedPermission(115, 2, 115);
        self::seedPermission(116, 2, 116);

        // Webinar 150 - 199
        self::seedPermission(150, 2, 150);
        self::seedPermission(151, 2, 151);
        self::seedPermission(152, 2, 152);
        self::seedPermission(153, 2, 153);
        self::seedPermission(154, 2, 154);
        self::seedPermission(155, 2, 155);
        self::seedPermission(156, 2, 156);
        self::seedPermission(157, 2, 157);
        self::seedPermission(158, 2, 158);

        // Categories 200 - 149
        self::seedPermission(200, 2, 200);
        self::seedPermission(201, 2, 201);
        self::seedPermission(202, 2, 202);
        self::seedPermission(203, 2, 203);
        self::seedPermission(204, 2, 204);
        self::seedPermission(205, 2, 205);
        self::seedPermission(206, 2, 206);
        self::seedPermission(207, 2, 207);
        self::seedPermission(208, 2, 208);

        // tags 250 - 299
        self::seedPermission(250, 2, 250);
        self::seedPermission(251, 2, 251);
        self::seedPermission(252, 2, 252);
        self::seedPermission(253, 2, 253);
        self::seedPermission(254, 2, 254);

        // Filters 300 - 349
        self::seedPermission(300, 2, 300);
        self::seedPermission(301, 2, 301);
        self::seedPermission(302, 2, 302);
        self::seedPermission(303, 2, 303);
        self::seedPermission(304, 2, 304);

        // Quiz 350 - 399
        self::seedPermission(350, 2, 350);
        self::seedPermission(351, 2, 351);
        self::seedPermission(352, 2, 352);
        self::seedPermission(353, 2, 353);
        self::seedPermission(354, 2, 354);
        self::seedPermission(355, 2, 355);
        self::seedPermission(356, 2, 356);
        self::seedPermission(357, 2, 357);

        // QuizResult 400 - 449
        self::seedPermission(400, 2, 400);
        self::seedPermission(401, 2, 401);
        self::seedPermission(402, 2, 402);
        self::seedPermission(403, 2, 403);
        self::seedPermission(404, 2, 404);
        self::seedPermission(405, 2, 405);

        // Certificates 450 - 499
        self::seedPermission(450, 2, 450);
        self::seedPermission(451, 2, 451);
        self::seedPermission(452, 2, 452);
        self::seedPermission(453, 2, 453);
        self::seedPermission(454, 2, 454);
        self::seedPermission(455, 2, 455);
        self::seedPermission(456, 2, 456);
        self::seedPermission(457, 2, 457);
        self::seedPermission(458, 2, 458);
        self::seedPermission(459, 2, 459);

        // Discount 500 - 549
        self::seedPermission(500, 2, 500);
        self::seedPermission(501, 2, 501);
        self::seedPermission(502, 2, 502);
        self::seedPermission(503, 2, 503);
        self::seedPermission(504, 2, 504);
        self::seedPermission(505, 2, 505);

        // Group 550 - 599
        self::seedPermission(550, 2, 550);
        self::seedPermission(551, 2, 551);
        self::seedPermission(552, 2, 552);
        self::seedPermission(553, 2, 553);
        self::seedPermission(554, 2, 554);

        // Payment Channels 600 - 649
        self::seedPermission(600, 2, 600);
        self::seedPermission(601, 2, 601);
        self::seedPermission(602, 2, 602);
        self::seedPermission(603, 2, 603);

        // setting
        self::seedPermission(650, 2, 650);
        self::seedPermission(651, 2, 651);
        self::seedPermission(652, 2, 652);
        self::seedPermission(653, 2, 653);
        self::seedPermission(654, 2, 654);
        self::seedPermission(655, 2, 655);
        self::seedPermission(656, 2, 656);

        // blog
        self::seedPermission(700, 2, 700);
        self::seedPermission(701, 2, 701);
        self::seedPermission(702, 2, 702);
        self::seedPermission(703, 2, 703);
        self::seedPermission(704, 2, 704);
        self::seedPermission(705, 2, 705);
        self::seedPermission(706, 2, 706);
        self::seedPermission(707, 2, 707);
        self::seedPermission(708, 2, 708);

        // sales
        self::seedPermission(750, 2, 750);
        self::seedPermission(751, 2, 751);
        self::seedPermission(752, 2, 752);
        self::seedPermission(753, 2, 753);
        self::seedPermission(754, 2, 754);

        // documents
        self::seedPermission(800, 2, 800);
        self::seedPermission(801, 2, 801);
        self::seedPermission(802, 2, 802);
        self::seedPermission(803, 2, 803);

        // payouts
        self::seedPermission(850, 2, 850);
        self::seedPermission(851, 2, 851);
        self::seedPermission(852, 2, 852);
        self::seedPermission(853, 2, 853);
        self::seedPermission(854, 2, 854);

        // offline payment
        self::seedPermission(900, 2, 900);
        self::seedPermission(901, 2, 901);
        self::seedPermission(902, 2, 902);
        self::seedPermission(903, 2, 903);
        self::seedPermission(904, 2, 904);

        // supports 950 - 999
        self::seedPermission(950, 2, 950);
        self::seedPermission(951, 2, 951);
        self::seedPermission(952, 2, 952);
        self::seedPermission(953, 2, 953);
        self::seedPermission(954, 2, 954);
        self::seedPermission(955, 2, 955);
        self::seedPermission(956, 2, 956);
        self::seedPermission(957, 2, 957);
        self::seedPermission(958, 2, 958);
        self::seedPermission(959, 2, 959);

        // Subscribes 1000 - 1049
        self::seedPermission(1000, 2, 1000);
        self::seedPermission(1001, 2, 1001);
        self::seedPermission(1002, 2, 1002);
        self::seedPermission(1003, 2, 1003);
        self::seedPermission(1004, 2, 1004);

        // Notifications 1050 - 1074
        self::seedPermission(1050, 2, 1050);
        self::seedPermission(1051, 2, 1051);
        self::seedPermission(1052, 2, 1052);
        self::seedPermission(1053, 2, 1053);
        self::seedPermission(1054, 2, 1054);
        self::seedPermission(1055, 2, 1055);
        self::seedPermission(1056, 2, 1056);
        self::seedPermission(1057, 2, 1057);
        self::seedPermission(1058, 2, 1058);
        self::seedPermission(1059, 2, 1059);
        self::seedPermission(1060, 2, 1060);

        // Noticeboards 1075 - 1099
        self::seedPermission(1075, 2, 1075);
        self::seedPermission(1076, 2, 1076);
        self::seedPermission(1077, 2, 1077);
        self::seedPermission(1078, 2, 1078);
        self::seedPermission(1079, 2, 1079);

        // promotions 1100 - 1149
        self::seedPermission(1100, 2, 1100);
        self::seedPermission(1101, 2, 1101);
        self::seedPermission(1102, 2, 1102);
        self::seedPermission(1103, 2, 1103);
        self::seedPermission(1104, 2, 1104);

        // testimonials 1150 - 1199
        self::seedPermission(1150, 2, 1150);
        self::seedPermission(1151, 2, 1151);
        self::seedPermission(1152, 2, 1152);
        self::seedPermission(1153, 2, 1153);
        self::seedPermission(1154, 2, 1154);

        // admin_advertising 1200 - 1229
        self::seedPermission(1200, 2, 1200);
        self::seedPermission(1201, 2, 1201);
        self::seedPermission(1202, 2, 1202);
        self::seedPermission(1203, 2, 1203);
        self::seedPermission(1204, 2, 1204);

        // admin newsletters 1230 - 1249
        self::seedPermission(1230, 2, 1230);
        self::seedPermission(1231, 2, 1231);
        self::seedPermission(1232, 2, 1232);
        self::seedPermission(1233, 2, 1233);
        self::seedPermission(1234, 2, 1234);
        self::seedPermission(1235, 2, 1235);

        // contact 1250 - 1299
        self::seedPermission(1250, 2, 1250);
        self::seedPermission(1251, 2, 1251);
        self::seedPermission(1252, 2, 1252);
        self::seedPermission(1253, 2, 1253);

        // special offers 1300 - 1349
        self::seedPermission(1300, 2, 1300);
        self::seedPermission(1301, 2, 1301);
        self::seedPermission(1302, 2, 1302);
        self::seedPermission(1303, 2, 1303);
        self::seedPermission(1304, 2, 1304);
        self::seedPermission(1305, 2, 1305);

        // pages 1350 - 1399
        self::seedPermission(1350, 2, 1350);
        self::seedPermission(1351, 2, 1351);
        self::seedPermission(1352, 2, 1352);
        self::seedPermission(1353, 2, 1353);
        self::seedPermission(1354, 2, 1354);
        self::seedPermission(1355, 2, 1355);

        // Comments 1400 - 1450
        self::seedPermission(1400, 2, 1400);
        self::seedPermission(1401, 2, 1401);
        self::seedPermission(1402, 2, 1402);
        self::seedPermission(1403, 2, 1403);
        self::seedPermission(1404, 2, 1404);
        self::seedPermission(1405, 2, 1405);
        self::seedPermission(1406, 2, 1406);
        self::seedPermission(1407, 2, 1407);
        self::seedPermission(1408, 2, 1408);
        self::seedPermission(1409, 2, 1409);
        self::seedPermission(1410, 2, 1410);

        // Reports 1400 - 1450
        self::seedPermission(1450, 2, 1450);
        self::seedPermission(1451, 2, 1451);
        self::seedPermission(1452, 2, 1452);
        self::seedPermission(1453, 2, 1453);
        self::seedPermission(1454, 2, 1454);
        self::seedPermission(1455, 2, 1455);

        // Additional Pages 1500 - 1549
        self::seedPermission(1500, 2, 1500);
        self::seedPermission(1501, 2, 1501);
        self::seedPermission(1502, 2, 1502);
        self::seedPermission(1503, 2, 1503);
        self::seedPermission(1504, 2, 1504);

        // reviews Pages 1600 - 1649
        self::seedPermission(1600, 2, 1600);
        self::seedPermission(1601, 2, 1601);
        self::seedPermission(1602, 2, 1602);
        self::seedPermission(1603, 2, 1603);
        self::seedPermission(1604, 2, 1604);

        // consultants Pages 1650 - 1674
        self::seedPermission(1650, 2, 1650);
        self::seedPermission(1651, 2, 1651);
        self::seedPermission(1652, 2, 1652);

        // Referrals 1675 - 1699
        self::seedPermission(1675, 2, 1675);
        self::seedPermission(1676, 2, 1676);
        self::seedPermission(1677, 2, 1677);
        self::seedPermission(1678, 2, 1678);

        // agora history 1700 - 1724
        self::seedPermission(1700, 2, 1700);
        self::seedPermission(1701, 2, 1701);
        self::seedPermission(1702, 2, 1702);

        // regions 1725 - 1749
        self::seedPermission(1725, 2, 1725);
        self::seedPermission(1726, 2, 1726);
        self::seedPermission(1727, 2, 1727);
        self::seedPermission(1728, 2, 1728);
        self::seedPermission(1729, 2, 1729);
        self::seedPermission(1730, 2, 1730);
        self::seedPermission(1731, 2, 1731);
        self::seedPermission(1732, 2, 1732);

        // Rewards 1750 - 1774
        self::seedPermission(1750, 2, 1750);
        self::seedPermission(1751, 2, 1751);
        self::seedPermission(1752, 2, 1752);
        self::seedPermission(1753, 2, 1753);
        self::seedPermission(1754, 2, 1754);

        // Registration packages 1775 - 1799
        self::seedPermission(1775, 2, 1775);
        self::seedPermission(1776, 2, 1776);
        self::seedPermission(1777, 2, 1777);
        self::seedPermission(1778, 2, 1778);
        self::seedPermission(1779, 2, 1779);
        self::seedPermission(1780, 2, 1780);
        self::seedPermission(1781, 2, 1781);
    }

    /**
     * NOTE(local-fix): stock seeder references section_ids that SectionsTableSeeder
     * does not create on fresh installs (e.g. 1410+). Skip those instead of
     * violating the permissions.section_id foreign key.
     */
    private static $existingSectionIds = null;

    private static function seedPermission(int $id, int $roleId, int $sectionId): void
    {
        if (self::$existingSectionIds === null) {
            self::$existingSectionIds = \App\Models\Section::pluck('id')->flip()->all();
        }

        if (!isset(self::$existingSectionIds[$sectionId])) {
            return;
        }

        \App\Models\Permission::updateOrCreate(['id' => $id], ['role_id' => $roleId, 'section_id' => $sectionId, 'allow' => 1]);
    }
}
