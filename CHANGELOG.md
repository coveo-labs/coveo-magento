# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2021-04-12
### Added
- Fixed cron job
- Fixed X-HTTP Fowards
- Fixed UserIp in all calls
- Fixed Analytics in all calls
- Fixed AddtoCartQuantity
- Fixed pr1ca, with full category
- Fixed Analytics request from main and search pages (different events)
- Fixed ClientId=VisitorId


## [1.9.99] - 2021-04-08
### Added
- Fixed cron job

## [1.9.98] - 2021-04-07
### Added
- Fixed cron job


## [1.9.97] - 2021-04-06
### Added
- Fixed index +1 for ClickAfterSearch
- Fixed crontab.xml

## [1.9.96] - 2021-04-06
### Added
- Fixed Crontab.xml


## [1.9.95] - 2021-04-02
### Added
- Fixed Query Suggest
- Fixed Did you mean
- Fixed You searched for
- Fixed responseTime
- Disconnected Coveo Index from full Magento Index

## [1.9.93] - 2021-03-30
### Added
- Fixed permanentid for indexing

## [1.9.92] - 2021-03-30
### Added
- Fixed recommendations hub name


## [1.9.91] - 2021-03-30

### Added
- Fixed recommendations

## [1.9.9] - 2021-03-30
### Added
- Element.ts earlier

## [1.9.8] - 2021-03-29
### Added
- Check if SKU is in result available
- Adding mlParameters, padding = trending to Recommendations


## [1.9.7] - 2021-03-26
### Added
- Fixed Mageplaza references

## [1.9.6] - 2021-03-25
### Added
- Fixed reference to SDK


## [1.9.5] - 2021-03-24
### Added
- Fixed front end analytics url

## [1.9.4] - 2021-03-24
### Added
- QS fixes

## [1.9.3] - 2021-03-24
### Added
- Added version to request
- Added QS

## [1.9.2] - 2021-03-23
### Added
- Fixes by Coveo

## [1.9.1] - 2021-03-22
### Added
- Fixes by Coveo

## [1.9.0] - 2021-03-09
### Added
- Add support for Coveo

## [1.8.1] - 2020-04-29
### Added
- Add compatibility for Magento 2.3
- Add compatibility for Mageplaza_LayeredNavigation

## [1.7.2] - 2019-12-11
### Fixed
- Fix userId not set when timeout occurs

## [1.7.1] - 2019-12-09
### Fixed
- Fix Elasticsearch compatibility when Mageplaza's modules are installed
- Fix pageview tracking on first user landing

## [1.7.0] - 2019-11-11
### Added
- Add customer ID tracking frontend side
- Add Elasticsearch search engine support 

## [1.6.1] - 2019-11-11
### Fixed
- Fix auto-detect store for redirect when path in not provided

## [1.6.0] - 2019-07-09
### Added
- Add button in backend to trigger attributes values index reindex
### Fixed
- Fix error on attributes values index update

## [1.5.0] - 2019-06-10
### Added
- Add redirect improvements to automatically switch store when "___redirect" query param is set to "auto"

## [1.4.0] - 2019-05-28
### Added
- Use store locale code for suggestion init params
- Add exported CSV column consistency
### Fixed
- Fix JSON interpolation for "

## [1.3.8] - 2019-05-14 
### Fixed
- Fix "all stores" indexer configuration
- Fix wrong store locale during indexing process

## [1.3.7] - 2019-04-30 
### Fixed
- Fix cacheable option for SpeechToTextTemplate block
### Added
- Upgrade bitbull/tooso-sdk to 1.3.0

## [1.3.6] - 2019-04-15 
### Fixed
- Fix bad userAgent processing

## [1.3.5] - 2019-04-10 
### Fixed
- Fix search from cli (using dummy data generator)

## [1.3.4] - 2019-04-10 
### Added
- Add cleaning on remote address when multiple proxy servers are present

## [1.3.3] - 2019-04-09 
### Added
- Add logger context tracking for debugging purpose
### Fixed
- Fix error on cart update tracking

## [1.3.2] - 2019-04-09 
### Fixed
- Fix TA inclusion tag

## [1.3.1] - 2019-04-03
### Added
- Add support for different AJAX/no-AJAX paginations 
### Fixed
- Fix clickAfterSearch productSku variable

## [1.3.0] - 2019-04-03
### Added
- Add button to clean log
- Add button to download log
- Add on click event handler with different approach
- Add 'data-search-id' logic to override search id when using AJAX
- Add reindex logging improvement
- Add AJAX response improvements

## [1.2.2] - 2019-03-27
### Fixed
- Fix subtree observer enable flag

## [1.2.1] - 2019-03-27
### Added
- Add subtree obsever to trigger clickAfterSearch events rebuild

## [1.2.0] - 2019-03-27
### Fixed
- Fix TA initialisation before inclusion
### Added
- Add Javascript SDK
- Add SpeechToText functionality
- Add cart product's quantity tracking
- Add AJAX support for click after search tracking
- Add product attributes export functionality

## [1.1.1] - 2019-03-19
### Fixed
- Fix single entity catalog reindex

## [1.1.0] - 2019-03-15
### Added
- Add catalog indexer
- Add tracking system
- Add frontend suggestion

## [1.0.2] - 2019-02-06
### Fixed
- Fix wrong search_request.xml syntax

## [1.0.1] - 2019-02-04
### Added
- Add support for Magento 2.2.5

## [1.0.0] - 2019-02-04
### First Release
