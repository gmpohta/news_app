<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
#[ORM\Table(name:"files")]
#[ORM\HasLifecycleCallbacks]
class File
{
    const UPLOAD_DIR = 'files';

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length:255)]
    private string $path;


    #[Assert\File(maxSize:"10m")]
    private $file;

    private string $dirStructure;

    private string $temp;

    public function getAbsolutePath(): ?string
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath(): ?string
    {
        return null === $this->path
            ? null
            : self::UPLOAD_DIR.'/'.$this->path;
    }

    private function getUploadRootDir(): string
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../public/'.self::UPLOAD_DIR;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function preUpload(): void
    {
        if (null === $this->getFile()) {
            return;
        }

        $this->dirStructure = (new \DateTime())->format('Y/m/d');
        $filename = sha1(uniqid(mt_rand(), true));
        $this->path = $this->dirStructure.'/'.$filename.'.'.$this->getFile()->getClientOriginalExtension();
    }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload(): void
    {
        if (null === $this->getFile()) {
            return;
        }

        $this->getFile()->move($this->getUploadRootDir().'/'.$this->dirStructure, $this->path);

        if (isset($this->temp)) {
            @unlink($this->getUploadRootDir().'/'.$this->temp);
            $this->temp = null;
        }

        $this->file = null;
    }


    #[ORM\PostRemove]
    public function removeUpload(): void
    {
        $file = $this->getAbsolutePath();

        if (file_exists($file)) {
            @unlink($file);
        }

        $this->file = null;
    }

    public function setFile(?UploadedFile $file): self
    {
        $this->file = $file;

        if (isset($this->path)) {
            $this->temp = $this->path;
            $this->path = null;
        }

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }
}
