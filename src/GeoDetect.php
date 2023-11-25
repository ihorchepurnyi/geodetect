<?php

namespace Hibit;

use Hibit\Country\CountryDatabase;
use Hibit\Country\CountryDetect;
use Hibit\Country\CountryRecord;
use Hibit\Exception\CountryFlagNotFoundException;
use Hibit\Flag\Extension;
use Hibit\Flag\Format;
use Hibit\Flag\Path;

/**
 * Class GeoDetect
 * @package Hibit\GeoDetect
 */
class GeoDetect
{
    private CountryDatabase $countryDatabase;

    public function __construct()
    {
        $this->countryDatabase = new CountryDatabase();
    }

    public function setCountriesDatabase(string $database): GeoDetect
    {
        $this->countryDatabase->set($database);

        return $this;
    }

    /**
     * @throws Exception\InvalidDatabaseException
     */
    public function getCountry(string $ip): CountryRecord
    {
        $countryDetect = new CountryDetect($this->countryDatabase);

        return $countryDetect->getByIp($ip);
    }

    /**
     * @throws Exception\CountryFlagNotFoundException
     */
    public static function getFlagByCountryRecord(CountryRecord $countryRecord, ?Format $format = Format::H20): string
    {
        $isoCode = $countryRecord->getIsoCode();

        if ($isoCode === null) {
            throw new CountryFlagNotFoundException('The country iso code is not valid');
        }

        return self::getFlagByIsoCode($isoCode, $format);
    }

    /**
     * @throws Exception\CountryFlagNotFoundException
     */
    public static function getFlagByIsoCode(string $countryIso2, ?Format $format = Format::H20): string
    {
        $filename = sprintf('%s%s.%s', Path::getFolderByFormat($format), strtolower($countryIso2), Extension::getByFormat($format));

        if (file_exists($filename) === false) {
            throw new CountryFlagNotFoundException(sprintf('Country flag not found for %s', $countryIso2));
        }

        $data = file_get_contents($filename);

        return 'data:image/' . Extension::getByFormat($format) . ';base64,' . base64_encode($data);
    }
}
