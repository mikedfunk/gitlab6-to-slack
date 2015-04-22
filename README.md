# Gitlab 6 to Slack

[Gitlab](https://about.gitlab.com/) CE did not have built-in slack integration
[until 6.7](https://about.gitlab.com/2014/03/21/gitlab-6-dot-7-released/). This
little app receives posts from GitLab and posts the necessary data to Slack in
order to create a message.

## Installation

1. [get composer](http://getcomposer.org)
2. clone this repo somewhere and cd in
3. `composer install` to install dependencies
4. Get your slack integration url for your channel
5. Duplicate `.env.dist` and rename to `.env`
6. Change the `SLACK_URL` value to the slack integration url
7. Set your GitLab web hook to `http://path-to-this/web/index.php`
8. Go back to GitLab and click "Test Hook". You should see a message. Yay!

You can run tests with `php vendor/bin/phpspec run`

## Todo

- [x] abstract config to phpdotenv file
- [x] Checks for .env var and proper post vars, tests on failure
- [x] Template for post string
- [x] exception catching for guzzle request
