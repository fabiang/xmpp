Feature: Connection
    Connecting to a jabber server

   Scenario: connect to jabber server
      Given Test connection adapter
      And Test response data for non-TLS
      When connecting
      Then should be connected
      And Stream start should be send
      When disconnecting
      Then Stream end should be send
      And should be disconnected

   Scenario: connect to jabber server with TLS support
      Given Test connection adapter
      And Test response data for TLS
      When connecting
      Then Starttls should be send
      And Stream start should be send 2 times

   Scenario: server closes connection
      Given Test connection adapter
      And Test response data for disconnect
      When connecting
      Then Stream end should be send
      And should be disconnected
