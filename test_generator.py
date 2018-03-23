basepath = 'tests/xomach00/'

while True:
    try:
        test = input('Test to generate: ')
    except EOFError:
        print('Exiting...')
        break

    try:
        with open(basepath+test+'.src', 'x') as src:
            print('.IPPcode18', file=src)
            print(test+'.src generated...')

        with open(basepath+test+'.in', 'x') as src:
            print(test+'.in generated...')

        with open(basepath+test+'.out', 'x') as src:
            print(test+'.out generated...')

        with open(basepath+test+'.rc', 'x') as src:
            print('0', file=src)
            print(test+'.rc generated...')

    except Exception as e:
        print(e)
