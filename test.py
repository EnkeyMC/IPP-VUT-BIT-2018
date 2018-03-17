import unittest



def load_tests(*args):
    loader = unittest.TestLoader()
    suite = loader.discover('unittests/python/', pattern='*.py')
    return unittest.TestSuite(suite)

def main():
    runner = unittest.TeR