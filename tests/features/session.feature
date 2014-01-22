Feature: Session handling
    Session should be initialized
   
   Scenario: Session is initialized
      Given Test connection adapter
      And Test response data for session
      When connecting
      Then request for session send
