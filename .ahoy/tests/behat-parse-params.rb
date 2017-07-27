require "minitest/autorun"
require "./dkan/.ahoy/.scripts/behat-parse-params.rb"

class TestBehatParseParams < MiniTest::Unit::TestCase

  def test_behat_parse_suite
    # return dkan for dkan features
    expected = "dkan"
    actual = behat_parse_suite "/var/www/dkan/test/features/my.feature"
    assert_equal expected, actual

    expected = "dkan"
    actual = behat_parse_suite "dkan/test/features/my.feature"
    assert_equal expected, actual

    # returns custom for custom features.
    expected = "custom"
    actual = behat_parse_suite "/var/www/config/tests/features/my.feature"
    assert_equal expected, actual

    expected = "custom"
    actual = behat_parse_suite "config/tests/features/my.feature"
    assert_equal expected, actual

    # returns dkan_starter for dkan_starter features or by default
    expected = "dkan_starter"
    actual = behat_parse_suite "/var/www/tests/features/my.feature"
    assert_equal expected, actual

    expected = "dkan_starter"
    actual = behat_parse_suite "tests/features/my.feature"
    assert_equal expected, actual

    expected = "dkan_starter"
    actual = behat_parse_suite nil 
    assert_equal expected, actual
  end

end
