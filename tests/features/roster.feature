Feature: Requesting and fetching roster list
    If connected roster should be requested and saved into options object


   Scenario: Requesting and parsing roster list
      Given Test connection adapter
      And Test response data for roster request
      When connecting
      And Roster request send
      Then options object should contain roster data
