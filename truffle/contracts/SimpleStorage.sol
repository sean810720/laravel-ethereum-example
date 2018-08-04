pragma solidity ^0.4.22;


contract SimpleStorage {
  bytes32 storedData;

  function set(bytes32 x) public {
    storedData = x;
  }

  function get() public view returns (bytes32) {
    return storedData;
  }
}
