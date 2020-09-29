@regression
@ticket-BB-9989
@fixture-OroProductBundle:ProductAttributesFixture.yml
Feature: Product attribute file
  In order to have custom attributes for Product entity
  As an Administrator
  I need to be able to add product attribute

  Scenario: Create product attribute
    Given I login as administrator
    And I go to Products/ Product Attributes
    When I click "Create Attribute"
    And I fill form with:
      | Field Name | FileField |
      | Type       | File      |
    And I click "Continue"
    Then I should see that "Product Attribute Frontend Options" does not contain "Searchable"
    And I should see that "Product Attribute Frontend Options" does not contain "Filterable"
    And I should see that "Product Attribute Frontend Options" does not contain "Sortable"
    And I should see "Allowed MIME Types" with options:
      | Value                    |
      | application/pdf          |
      | application/vnd.ms-excel |
      | application/msword       |
      | application/zip          |
      | image/gif                |
      | image/jpeg               |
      | image/png                |
    When I fill form with:
      | File Size (MB)        | 10                                       |
      | Allowed MIME types    | [application/pdf, image/png, image/jpeg] |
    And I save and close form
    Then I should see "Attribute was successfully saved" flash message

    When I click update schema
    Then I should see "Schema updated" flash message

  Scenario: Update product family with new attribute
    Given I go to Products/ Product Families
    When I click "Edit" on row "default_family" in grid
    And I fill "Product Family Form" with:
      | Attributes | [FileField] |
    And I save and close form
    Then I should see "Successfully updated" flash message

  Scenario: Update product
    Given I go to Products/ Products
    When I click "Edit" on row "SKU123" in grid
    And I fill "Product Form" with:
      | FileField | tiger.svg |
    And I save and close form
    Then I should see "Product Form" validation errors:
      | FileField | The mime type of the file is invalid ("image/svg+xml"). Allowed mime types are "application/pdf", "image/jpeg", "image/png". |
    Then I fill "Product Form" with:
      | FileField | cat1.jpg |
    And I save and close form
    Then I should see "Product has been saved" flash message

  Scenario: Check file attribute is available at store front
    Given I login as AmandaRCole@example.org buyer
    When I type "SKU123" in "search"
    And I click "Search Button"
    And I click "View Details" for "SKU123" product
    Then I should see "cat1.jpg" link with the url matches "/attachment/.+?\.jpg"
    And I should not see "cat1.jpg" link with the url matches "/admin/"

  Scenario: Delete product attribute
    Given I login as administrator
    And I go to Products/ Product Attributes
    When I click Remove "FileField" in grid
    Then I should see "Are you sure you want to delete this attribute?"
    And I click "Yes"
    Then I should see "Attribute successfully deleted" flash message
    And I should see "Update schema"
