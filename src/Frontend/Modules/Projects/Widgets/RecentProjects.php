<?php

namespace Frontend\Modules\Projects\Widgets;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\Widget as FrontendBaseWidget;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Core\Engine\Navigation as FrontendNavigation;
use Frontend\Modules\Projects\Engine\Model as FrontendProjectsModel;

/**
 * This is a widget to show the most recent projects
 *
 * @author Tim van Wolfswinkel <tim@webleads.nl>
 */
class RecentProjects extends FrontendBaseWidget
{
    /**
     * Execute the extra
     */
    public function execute()
    {
        // call parent
        parent::execute();

        $this->loadTemplate();
        $this->parse();
    }

    /**
     * Parse
     */
    private function parse()
    {
        // get module setting amount of recent projects
        $amountOfRecentProjects = FrontendModel::getModuleSetting('Projects', 'amount_of_recent_products', 3);
        $this->tpl->assign('widgetProjectsRecent', FrontendProjectsModel::getRecentProjects($amountOfRecentProjects));
    }
}
