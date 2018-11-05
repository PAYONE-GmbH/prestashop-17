--
-- PAYONE Prestashop Connector is free software: you can redistribute it and/or modify
-- it under the terms of the GNU Lesser General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- PAYONE Prestashop Connector is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU Lesser General Public License for more details.
--
-- You should have received a copy of the GNU Lesser General Public License
-- along with PAYONE Prestashop Connector. If not, see <http://www.gnu.org/licenses/>.
--
-- PHP version 5
--
-- @category  Payone
-- @package   fcpayone
-- @author    patworx multimedia GmbH <service@patworx.de>
-- @copyright 2003 - 2016 Payone GmbH
-- @license   <http://www.gnu.org/licenses/> GNU Lesser General Public License
-- @link      http://www.payone.de
--

CREATE TABLE IF NOT EXISTS `###TABLE_REQUEST###` (
    `id_request` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `reference` varchar(255) not null,
    `request` text not null,
    `response` text not null,
    `status` varchar(255) not null,
    `txid` int(11) unsigned not null,
    `userid` int(11) unsigned not null,
    `date` datetime NOT NULL,
    PRIMARY KEY  (`id_request`)
) ENGINE=###TABLE_ENGINE### DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `###TABLE_TRANSACTION###` (
    `id_transaction` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `id_customer` int(10) unsigned NOT NULL,
    `id_order` int(10) unsigned NOT NULL,
    `reference` varchar(255) not null,
    `sequencenumber` int(11) not null,
    `txid` int(11) unsigned not null,
    `txaction` varchar(255) not null,
    `userid` int(11) unsigned not null,
    `data` text not null,
    `date` datetime NOT NULL,
    PRIMARY KEY  (`id_transaction`)
) ENGINE=###TABLE_ENGINE### DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `###TABLE_USER###` (
    `id_customer` int(10) unsigned NOT NULL,
    `userid` int(11) unsigned not null,
    `date` datetime NOT NULL,
    PRIMARY KEY  (`id_customer`, `userid`)
) ENGINE=###TABLE_ENGINE### DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `###TABLE_MANDATE###` (
    `id_mandate` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `mandate_identifier` varchar(255) NOT NULL,
    `id_order` int(10) unsigned NOT NULL,
    `file_content` blob,
    `date` datetime NOT NULL,
    PRIMARY KEY  (`id_mandate`)
) ENGINE=###TABLE_ENGINE### DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `###TABLE_REFERENCE###` (
    `reference` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `reference_prefix` varchar(255) NOT NULL,
    `txid` int(11) unsigned not null,
    `date` datetime NOT NULL,
    PRIMARY KEY  (`reference`,`reference_prefix`)
) ENGINE=###TABLE_ENGINE### DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `###TABLE_ORDER###` (
    `id_order` int(11) unsigned NOT NULL,
    `reference_order` varchar(255) NOT NULL,
    `reference` varchar(255) NOT NULL,
    `paymentid` varchar(255) NOT NULL,
    `requesttype` varchar(255) NOT NULL,
    `mode` varchar(255) NOT NULL,
    `txid` int(11) unsigned not null,
    `userid` int(11) unsigned not null,
    `date` datetime NOT NULL,
    PRIMARY KEY  (`id_order`)
) ENGINE=###TABLE_ENGINE### DEFAULT CHARSET=utf8;