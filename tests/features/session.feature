Feature: Support for binding a jid
    If server sends support for binding jid we should request it from the server

   Scenario: Binding jid
      Given Test connection adapter
      And Test response data for bind
      When connecting
      Then request for binding send
      And Jid is set to options object