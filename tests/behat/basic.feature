@tiny @tiny_embedmediasite
Feature: Basic tests for Embed mediasite

  @javascript
  Scenario: Plugin tiny_embedmediasite appears in the list of installed additional plugins
    Given I log in as "admin"
    When I navigate to "Plugins > Plugins overview" in site administration
    And I follow "Additional plugins"
    Then I should see "Embed Mediasite video"
    And I should see "tiny_embedmediasite"
