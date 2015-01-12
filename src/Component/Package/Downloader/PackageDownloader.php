<?php

namespace Pagekit\Component\Package\Downloader;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TransferException;
use Pagekit\Component\Filesystem\Archive\Zip;
use Pagekit\Component\Filesystem\File;
use Pagekit\Component\Package\Exception\ArchiveExtractionException;
use Pagekit\Component\Package\Exception\ChecksumVerificationException;
use Pagekit\Component\Package\Exception\DownloadErrorException;
use Pagekit\Component\Package\Exception\NotWritableException;
use Pagekit\Component\Package\Exception\UnauthorizedDownloadException;
use Pagekit\Component\Package\Exception\UnexpectedValueException;
use Pagekit\Component\Package\PackageInterface;

class PackageDownloader implements DownloaderInterface
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var File
     */
    protected $file;

    /**
     * Constructor.
     *
     * @param File            $file
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client = null, File $file = null)
    {
        $this->client = $client ?: new Client;
        $this->file   = $file  ?: new File;
    }

    /**
     * {@inheritdoc}
     */
    public function download(PackageInterface $package, $path)
    {
        if (!$url = $package->getDistUrl()) {
            throw new UnexpectedValueException("The given package is missing url information");
        }

        $this->downloadFile($path, $url, $package->getDistSha1Checksum());
    }

    /**
     * Download a package file.
     *
     * @param  string $path
     * @param  string $url
     * @param  string $shasum
     * @throws \Exception
     */
    public function downloadFile($path, $url, $shasum = '')
    {
        $file = $path.'/'.uniqid();

        try {

            $data = $this->client->get($url)->getBody();

            if ($shasum && sha1($data) !== $shasum) {
                throw new ChecksumVerificationException("The file checksum verification failed");
            }

            if (!$this->file->makeDir($path) || !file_put_contents($file, $data)) {
                throw new NotWritableException("The path is not writable ($path)");
            }

            if (Zip::extract($file, $path) !== true) {
                throw new ArchiveExtractionException("The file extraction failed");
            }

            $this->file->delete($file);

        } catch (\Exception $e) {

            $this->file->delete($path);

            if ($e instanceof TransferException) {

                if ($e instanceof BadResponseException) {
                    throw new UnauthorizedDownloadException("Unauthorized download ($url)");
                }

                throw new DownloadErrorException("The file download failed ($url)");
            }

            throw $e;
        }
    }
}
