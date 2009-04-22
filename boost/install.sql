-- OpenID module for phpWebSite
--
-- See docs/CREDITS for copyright information
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
--
-- $Id: install.sql,v 1.2 2008/02/06 04:40:21 blindman1344 Exp $

CREATE TABLE openid_mapping (
  id INT NOT NULL,
  openid_identifier VARCHAR(255) NOT NULL,
  user_id INT DEFAULT '0' NOT NULL,
  PRIMARY KEY (id)
);
