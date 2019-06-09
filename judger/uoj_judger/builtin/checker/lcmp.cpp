#include "testlib.h"
#include <string>

using namespace std;

void rtrim(string &s)
{
	if(!s.empty()){
		s.erase(s.find_last_not_of(" ") + 1);
	}
}

int main(int argc, char * argv[])
{
    setName("compare files, ignoring the space at the end of the line and the empty lines at the end of the file");
    registerTestlibCmd(argc, argv);

    int n = 0;
    std::string j,p;
    while (!ans.eof()) 
    {
        j = ans.readString();
        if (j == "" && ans.eof())
        	break;
        p = ouf.readString();
        rtrim(j);
        rtrim(p);

        n++;
        if (j!=p)
            quitf(_wa, "%d%s lines differ - expected: '%s', found: '%s'", n, englishEnding(n).c_str(), compress(j).c_str(), compress(p).c_str());
    }
    
    quitf(_ok, "%d lines", n);
}
