<?php
namespace App\View\Components\LandingV1;

use Illuminate\View\Component;

class CourseCard extends Component
{
    public $title;
    public $description;
    public $teacherName;
    public $teacherAvatar;
    public $price;
    public $image;
    public $slug;

    public function __construct($title = '', $description = '', $teacherName = '', $teacherAvatar = '', $price = '', $image = '', $slug = '')
    {
        $this->title = $title;
        $this->description = $description;
        $this->teacherName = $teacherName;
        $this->teacherAvatar = $teacherAvatar;
        $this->price = $price;
        $this->image = $image;
        $this->slug = $slug;
    }

    public function render()
    {
        return view('landing_v1.components.course-card');
    }
}
?>
