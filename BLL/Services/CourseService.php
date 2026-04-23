<?php
require_once __DIR__ . '/../../DAL/Repository/CourseRepository.php';
require_once __DIR__ . '/../../DAL/DTOs/CourseDTOs.php';
require_once __DIR__ . '/../../BLL/Mappers/CourseMapper.php';
class CourseService 
{
    private $courseRepo;
    private $mapper;
    public function __construct( )
    {
        $this->courseRepo = new CourseRepository();
        $this->mapper = new CourseMapper();
    }

    public function create(CourseRequestDTO $courseDTO)
    {
        $courseEntity = $this->mapper->toEntity($courseDTO);
        return $this->courseRepo->getById($this->courseRepo->create($courseEntity));
    }

    public function getById($id)
    {
        return ;
    }
}