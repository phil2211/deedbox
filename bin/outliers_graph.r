#!/usr/bin/Rscript
args <- commandArgs(TRUE)
FILE=args[1]
TITLE=args[2]
args=args[-2]
X = as.numeric(c(args))
png(FILE)
boxplot(X, range=1.5, main=TITLE)
dev.off()
