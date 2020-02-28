# Hub Data Tidy

Designed for use with a [Parent Progress View](https://github.com/TestValleySchool/moodle-report-parentprogressview) deployment where a WordPress site "The Hub"
is used as a data warehouse for data extracted from a ~~popular~~ common Management Information
System (MIS). This plugin allows us to periodically tidy the data warehouse WordPress instance
of old data to ensure it stays performant.

This plugin enumerates custom post types and permits the removal of old content based on
date or association with a postmeta object with a given prefix (used to clear data associated
with a username prefix).

## Usage

A WP-Admin interface is provided which appears upon plugin activation.

Additionally, a WP-CLI interface is provided which improves the experience for very large
batch sizes or for automated use.

	NAME

	  wp hub-data-tidy

	DESCRIPTION

	  Process the tidying up of Hub data through the WP_CLI interface.

	SYNOPSIS

	  wp hub-data-tidy

	  --wp-post-types=<post-types>
	 : A comma-separated set of the WordPress post type slugs to tidy (e.g. 'achievements,attendance-marks')

	  --batch-size=<batch-size>
	 : A maximum number of posts to process in this execution run of the command.

	  --simulate=<simulate>
	 : Simulate mode is on by default. Pass 'off' or 'false' to actually remove content from WordPress.

	  --attached-username-prefix=<username-prefix>
	 : Only remove WP posts that have a meta value such that they are associated with usernames beginning with this string.

	  --date=<date>
	 : A YYYY-MM-DD string, where any content that was created on or after this date will not be selected for removal.


## Licence

[In common with the Moodle software from which it derives](https://docs.moodle.org/dev/License), Parent Progress View and Hub Data Tidy are available under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

Please see `LICENSE.md` in this repository for full licence text.
