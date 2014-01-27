Feature: Session handling
    Session should be initialized
   
   Scenario: Session is initialized
      Given Test connection adapter
      And Test response data for session
      When manipulating id
      And connecting
      Then request for session send

   Scenario: Session response is empty
      Given Test connection adapter
      And Test response data for empty session
      And manipulating id
      When connecting
      Then session listener is not blocking
