<?php
/**
 * Nextcloud - Tasks
 *
 * @author Raimund Schlüßler
 * @copyright 2019 Raimund Schlüßler <raimund.schluessler@mailbox.org>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Tasks\AppInfo;

use OCA\Tasks\Dashboard\TasksWidget;
use OCA\Tasks\Listeners\BeforeTemplateRenderedListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCA\DAV\CalDAV\CalDavBackend;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use OCP\Defaults;
use OCP\IDBConnection;
use OCP\IUser;

class Application extends App implements IBootstrap {
	/** @var string */
	public const APP_ID = 'tasks';
	public const TASKS_CALENDAR_URI = 'tasks';
	public const TASKS_CALENDAR_NAME = 'Tasks';
	public const TASKS_CALENDAR_COMPONENT = 'VTODO';

	/**
	 * @param array $params
	 */
	public function __construct(array $params = []) {
		parent::__construct(self::APP_ID, $params);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerDashboardWidget(TasksWidget::class);

		$context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
	}

	public function boot(IBootContext $context): void {
		$context->injectFn([$this, 'createTasksCalendar']);
	}

	public function createTasksCalendar(CalDavBackend $calDav, IDBConnection $db, Defaults $themingDefaults, EventDispatcherInterface $dispatcher): void {
		$dispatcher->addListener(IUser::class . '::firstLogin', function (GenericEvent $event) use ($calDav, $themingDefaults, $db) {
			$user = $event->getSubject();
			if (!$user instanceof IUser) {
				return;
			}
			$userId = $user->getUID();
			$principal = 'principals/users/' . $userId;
			$calendar = $calDav->getCalendarByUri($principal, self::TASKS_CALENDAR_URI);
			$query = $db->getQueryBuilder();
			$query->select('uri')->from('calendars')
					->where($query->expr()->eq('uri', $query->createNamedParameter(self::TASKS_CALENDAR_URI)))
					->andWhere($query->expr()->eq('principaluri', $query->createNamedParameter($principal)))
					->andWhere($query->expr()->eq('components', $query->createNamedParameter(self::TASKS_CALENDAR_COMPONENT)))
					->setMaxResults(1);
			$stmt = $query->executeQuery();
			$row = $stmt->fetch();
			$stmt->closeCursor();
			if ($row === false) {
				$calDav->createCalendar($principal, self::TASKS_CALENDAR_URI, [
					'{DAV:}displayname' => self::TASKS_CALENDAR_NAME,
					'{http://apple.com/ns/ical/}calendar-color' => $themingDefaults->getColorPrimary(),
					'components' => self::TASKS_CALENDAR_COMPONENT
				]);
			}
		});
	}



}
