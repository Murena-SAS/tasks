/**
 * Nextcloud - Tasks
 *
 * @copyright Copyright (c) 2021 Jakob Röhrl
 *
 * @author Jakob Röhrl <jakob.roehrl@web.de>
 *
 * @license AGPL-3.0-or-later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

import Dashboard from './views/Dashboard.vue'
import store from './store/store.js'

import './css/dashboard.scss'

import { generateFilePath } from '@nextcloud/router'
import { getRequestToken } from '@nextcloud/auth'

import { createApp } from 'vue'

// eslint-disable-next-line
__webpack_nonce__ = btoa(getRequestToken())

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath('tasks', '', 'js/')

document.addEventListener('DOMContentLoaded', () => {
	OCA.Dashboard.register('tasks', (el) => {
		const item = createApp(Dashboard)
			.use(store)
			.mount(el)
		return item
	})
})
