<?php
declare (strict_types=1);

namespace App\Entity\Config;

use Kernel\Component\ToArray;

class Site
{
    use ToArray;

    public string $logo;
    public string $title;
    public string $keywords;
    public string $description;
    public string $icp;
    public string $pcTheme;
    public string $mobileTheme;
    public string $noticeBanner;
    public string $notice;
    public string $bgImage;

    public function __construct(array $values)
    {
        $this->logo = $values['logo'];
        $this->title = $values['title'];
        $this->keywords = $values['keywords'];
        $this->description = $values['description'];
        $this->icp = $values['icp'];
        $this->pcTheme = $values['pc_theme'];
        $this->mobileTheme = $values['mobile_theme'];
        $this->notice = $values['notice'];
        $this->noticeBanner = $values['notice_banner'];
        $this->bgImage = $values['bg_image'] ?? "";
    }
}