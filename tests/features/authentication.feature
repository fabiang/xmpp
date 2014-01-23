Feature: Authentication
    Authenticate to jabber server

   Scenario: plain text authentication
      Given Test connection adapter
      And Test response data for plain
      When connecting
      Then plain authentication element should be send
      And should be authenticated
      And Stream start should be send 2 times

   Scenario: wrong user or password
      Given Test connection adapter
      And Test response data for authentication failure
      And exceptions are catched when connecting
      When connecting
      Then a authorization exception should be catched
